<?php

namespace LaravelLux\Html;

use BadMethodCallException;
use DateTimeInterface;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Traits\Macroable;

class FormBuilder
{
    use Macroable, Componentable {
        Macroable::__call as macroCall;
        Componentable::__call as componentCall;
    }

    /**
     * The HTML builder instance.
     *
     * @var HtmlBuilder
     */
    protected HtmlBuilder $html;

    /**
     * The URL generator instance.
     *
     * @var UrlGenerator
     */
    protected UrlGenerator $url;

    /**
     * The View factory instance.
     *
     * @var Factory
     */
    protected Factory $view;

    /**
     * The CSRF token used by the form builder.
     *
     * @var null|string
     */
    protected ?string $csrfToken;

    /**
     * Consider Request variables while auto fill.
     * @var bool
     */
    protected bool $considerRequest = false;

    /**
     * The session store implementation.
     *
     * @var Session
     */
    protected Session $session;

    /**
     * The current model instance for the form.
     *
     * @var mixed
     */
    protected mixed $model = null;

    /**
     * An array of label names we've created.
     *
     * @var array
     */
    protected array $labels = [];

    protected ?Request $request;

    /**
     * The reserved form open attributes.
     *
     * @var array
     */
    protected array $reserved = ['method', 'url', 'route', 'action', 'files'];

    /**
     * The form methods that should be spoofed, in uppercase.
     *
     * @var array
     */
    protected array $spoofedMethods = ['DELETE', 'PATCH', 'PUT'];

    /**
     * The types of inputs to not fill values on by default.
     *
     * @var array
     */
    protected array $skipValueTypes = ['file', 'password', 'checkbox', 'radio'];


    /**
     * Input Type.
     *
     * @var string|null
     */
    protected ?string $type = null;

    /**
     * Inject the csrf_token hidden input automatically
     * @var bool
     */
    protected bool $injectCsrfToken = true;

    /**
     * Create a new form builder instance.
     *
     * @param HtmlBuilder $html
     * @param UrlGenerator $url
     * @param Factory $view
     * @param string $csrfToken
     * @param Request|null $request
     */
    public function __construct(HtmlBuilder $html, UrlGenerator $url, Factory $view, null|string $csrfToken = null, Request $request = null)
    {
        $this->url = $url;
        $this->html = $html;
        $this->view = $view;
        $this->csrfToken = $csrfToken;
        $this->request = $request;
    }

    /**
     * Open up a new HTML form.
     *
     * @param array $options
     *
     * @return HtmlString|string
     */
    public function open(array $options = []): HtmlString|string
    {
        $method = Arr::get($options, 'method', 'post');
        $method_attributes = [];

        if (is_array($method)) {
            $method_attributes = $method;
            $method = Arr::get($method, 'value', 'post');
        }

        // We need to extract the proper method from the attributes. If the method is
        // something other than GET or POST we'll use POST since we will spoof the
        // actual method since forms don't support the reserved methods in HTML.
        $attributes['method'] = $this->getMethod($method);
        $attributes['action'] = $this->getAction($options);

        $attributes['accept-charset'] = 'UTF-8';

        /**
         * If the method is PUT, PATCH or DELETE we will need to add a spoofer hidden
         * field that will instruct the Symfony request to pretend the method is a
         * different method than it actually is, for convenience from the forms.
         **/
        $append = $this->getAppendage($method, $method_attributes);

        if (isset($options['files']) && $options['files']) {
            $options['enctype'] = 'multipart/form-data';
        }

        // Finally we're ready to create the final form HTML field. We will attribute
        // format the array of attributes. We will also add on the appendage which
        // is used to spoof requests for this PUT, PATCH, etc. methods on forms.
        $attributes = array_merge(

          $attributes, Arr::except($options, $this->reserved)

        );

        /**
        ** Finally, we will concatenate all the attributes into a single string so
        ** we can build out the final form open statement. We'll also append on an
        ** extra value for the hidden _method field if it's needed for the form.
        **/
        $attributes = $this->html->attributes($attributes, 'form');

        return $this->toHtmlString('<form' . $attributes . '>' . $append);
    }

    /**
     * Create a new model based form builder.
     *
     * @param mixed $model
     * @param array $options
     *
     * @return string|HtmlString
     */
    public function model(mixed $model, array $options = []): string|HtmlString
    {
        $this->model = $model;

        return $this->open($options);
    }

    /**
     * Set the model instance on the form builder.
     *
     * @param  mixed $model
     *
     * @return void
     */
    public function setModel(mixed $model): void
    {
        $this->model = $model;
    }

    /**
     * Get the current model instance on the form builder.
     *
     * @return mixed $model
     */
    public function getModel(): mixed
    {
        return $this->model;
    }

    /**
     * Close the current form.
     *
     * @return string|HtmlString
     */
    public function close(): string|HtmlString
    {
        $this->labels = [];

        $this->model = null;

        return $this->toHtmlString('</form>');
    }

    /**
     * Generate a hidden field with the current CSRF token.
     *
     * @return HtmlString|string
     */
    public function token(): HtmlString|string
    {
        $token = ! empty($this->csrfToken) ? $this->csrfToken : $this->session->token();

        return $this->hidden('_token', $token);
    }

    /**
     * Enable or disable automatic csrf_token injection
     *
     * @param bool $inject_token
     * @return self
     */
    public function withoutCsrf(bool $inject_token = false): static
    {
        $this->injectCsrfToken = $inject_token;

        return $this;
    }

    /**
     * Create a form label element.
     *
     * @param string $name
     * @param string|null $value
     * @param array|null $options
     * @param bool|null $escape_html
     *
     * @return HtmlString|string
     */
    public function label(string $name, string|null $value = null, array|null $options = [], bool|null $escape_html = true): HtmlString|string
    {
        $this->labels[] = $name;

        $options = $this->html->attributes($options, 'label');

        $value = $this->formatLabel($name, $value);

        if ($escape_html) {
            $value = $this->html->entities($value);
        }

        return $this->toHtmlString('<label for="' . $name . '"' . $options . '>' . $value . '</label>');
    }

    /**
     * Format the label value.
     *
     * @param string $name
     * @param string|null $value
     *
     * @return string
     */
    protected function formatLabel(string $name, ?string $value): string
    {
        return $value ?: ucwords(str_replace('_', ' ', $name));
    }

    /**
     * Create a form input field.
     *
     * @param string $type
     * @param string|null $name
     * @param string|null $value
     * @param array $options
     *
     * @return string|HtmlString
     */
    public function input(string $type, string|null $name, string|null $value = null, array $options = []): string|HtmlString
    {
        $this->type = $type;

        if (! isset($options['name'])) {
            $options['name'] = $name;
        }

        // We will get the appropriate value for the given field. We will look for the
        // value in the session for the value in the old input data then we'll look
        // in the model instance if one is set. Otherwise, we will just use empty.
        $id = $this->getIdAttribute($name, $options);

        if (! in_array($type, $this->skipValueTypes)) {
            $value = $this->getValueAttribute($name, $value);
        }

        // Once we have the type, value, and ID we can merge them into the rest of the
        // attributes array, so we can convert them into their HTML attribute format
        // when creating the HTML element. Then, we will return the entire input.
        $merge = compact('type', 'value', 'id');

        $options = array_merge($options, $merge);

        return $this->toHtmlString('<input' . $this->html->attributes($options, $type) . '>');
    }

    /**
     * Create a text input field.
     *
     * @param string $name
     * @param string|null $value
     * @param array $options
     *
     * @return string|HtmlString
     */
    public function text(string $name, string|null $value = null, array $options = []): string|HtmlString
    {
        return $this->input('text', $name, $value, $options);
    }

    /**
     * Create a password input field.
     *
     * @param string $name
     * @param array $options
     *
     * @return string|HtmlString
     */
    public function password(string $name, array $options = []): string|HtmlString
    {
        return $this->input('password', $name, '', $options);
    }

    /**
     * Create a range input field.
     *
     * @param string $name
     * @param string|null $value
     * @param array $options
     *
     * @return string|HtmlString
     */
    public function range(string $name, string|null $value = null, array $options = []): string|HtmlString
    {
        return $this->input('range', $name, $value, $options);
    }

    /**
     * Create a hidden input field.
     *
     * @param string $name
     * @param string|null $value
     * @param array $options
     *
     * @return string|HtmlString
     */
    public function hidden(string $name, string|null $value = null, array $options = []): string|HtmlString
    {
        return $this->input('hidden', $name, $value, $options);
    }

    /**
     * Create a search input field.
     *
     * @param string $name
     * @param string|null $value
     * @param array $options
     *
     * @return string|HtmlString
     */
    public function search(string $name, string|null $value = null, array $options = []): string|HtmlString
    {
        return $this->input('search', $name, $value, $options);
    }

    /**
     * Create an e-mail input field.
     *
     * @param string $name
     * @param string|null $value
     * @param array $options
     *
     * @return string|HtmlString
     */
    public function email(string $name, string|null $value = null, array $options = []): string|HtmlString
    {
        return $this->input('email', $name, $value, $options);
    }

    /**
     * Create a tel input field.
     *
     * @param string $name
     * @param string|null $value
     * @param array $options
     *
     * @return string|HtmlString
     */
    public function tel(string $name, string|null $value = null, array $options = []): string|HtmlString
    {
        return $this->input('tel', $name, $value, $options);
    }

    /**
     * Create a number input field.
     *
     * @param string $name
     * @param string|null $value
     * @param array $options
     *
     * @return string|HtmlString
     */
    public function number(string $name, string|null $value = null, array $options = []): string|HtmlString
    {
        return $this->input('number', $name, $value, $options);
    }

    /**
     * Create a date input field.
     *
     * @param string $name
     * @param string|DateTimeInterface|null $value
     * @param array $options
     *
     * @return string|HtmlString
     */
    public function date(string $name, string|DateTimeInterface|null $value = null, array $options = []): string|HtmlString
    {
        $value ??= $this->getValueAttribute($name, $value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }

        return $this->input('date', $name, $value, $options);
    }

    /**
     * Create a datetime input field.
     *
     * @param string $name
     * @param string|DateTimeInterface|null $value
     * @param array $options
     *
     * @return HtmlString|string
     */
    public function datetime(string $name, string|DateTimeInterface|null $value = null, array $options = []): HtmlString|string
    {
        if ($value instanceof DateTimeInterface) {
            $value = $value->format(DateTimeInterface::RFC3339);
        }

        return $this->input('datetime', $name, $value, $options);
    }

    /**
     * Create a datetime-local input field.
     *
     * @param string $name
     * @param string|DateTimeInterface|null $value
     * @param array $options
     *
     * @return HtmlString|string
     * @noinspection PhpUnused
     */
    public function datetimeLocal(string $name, string|DateTimeInterface|null $value = null, array $options = []): HtmlString|string
    {
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d\TH:i');
        }

        return $this->input('datetime-local', $name, $value, $options);
    }

    /**
     * Create a time input field.
     *
     * @param string $name
     * @param string|DateTimeInterface|null $value
     * @param array $options
     *
     * @return HtmlString|string
     */
    public function time(string $name, string|DateTimeInterface|null $value = null, array $options = []): HtmlString|string
    {
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('H:i');
        }

        return $this->input('time', $name, $value, $options);
    }

    /**
     * Create a url input field.
     *
     * @param string $name
     * @param string|null $value
     * @param array $options
     *
     * @return HtmlString|string
     */
    public function url(string $name, string|null $value = null, array $options = []): HtmlString|string
    {
        return $this->input('url', $name, $value, $options);
    }

    /**
     * Create a week input field.
     *
     * @param string $name
     * @param string|DateTimeInterface|null $value
     * @param array $options
     *
     * @return HtmlString|string
     */
    public function week(string $name, string|DateTimeInterface|null $value = null, array $options = []): HtmlString|string
    {
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-\WW');
        }

        return $this->input('week', $name, $value, $options);
    }

    /**
     * Create a file input field.
     *
     * @param string $name
     * @param array $options
     *
     * @return HtmlString|string
     */
    public function file(string $name, array $options = []): HtmlString|string
    {
        return $this->input('file', $name, null, $options);
    }

    /**
     * Create a textarea input field.
     *
     * @param string $name
     * @param string|null $value
     * @param array $options
     *
     * @return HtmlString|string
     */
    public function textarea(string $name, string|null $value = null, array $options = []): HtmlString|string
    {
        $this->type = 'textarea';

        if (! isset($options['name'])) {
            $options['name'] = $name;
        }

        // Next we will look for the rows and cols attributes, as each of these are put
        // on the textarea element definition. If they are not present, we will just
        // assume some sane default values for these attributes for the developer.
        $options = $this->setTextAreaSize($options);

        $options['id'] = $this->getIdAttribute($name, $options);

        $value = (string) $this->getValueAttribute($name, $value);

        unset($options['size']);

        /**
         * Next we will convert the attributes into a string form. Also, we have removed
         * the size attribute, as it was merely a short-cut for the rows and cols on
         * the element. Then we'll create the final textarea elements HTML for us.
         **/
        $options = $this->html->attributes($options, 'textarea');

        return $this->toHtmlString('<textarea' . $options . '>' . e($value, false). '</textarea>');
    }

    /**
     * Set the text area size on the attributes.
     *
     * @param array $options
     *
     * @return array
     */
    protected function setTextAreaSize(array $options): array
    {
        if (isset($options['size'])) {
            return $this->setQuickTextAreaSize($options);
        }

        // If the "size" attribute was not specified, we will just look for the regular
        // columns and rows attributes, using sane defaults if these do not exist on
        // the attributes array. We'll then return this entire options array back.
        $cols = Arr::get($options, 'cols', 50);

        $rows = Arr::get($options, 'rows', 10);

        return array_merge($options, compact('cols', 'rows'));
    }

    /**
     * Set the text area size using the quick "size" attribute.
     *
     * @param array $options
     *
     * @return array
     */
    protected function setQuickTextAreaSize(array $options): array
    {
        $segments = explode('x', $options['size']);

        return array_merge($options, ['cols' => $segments[0], 'rows' => $segments[1]]);
    }

    /**
     * Create a select box field.
     *
     * @param string $name
     * @param array|Collection $list
     * @param bool|string|array|Collection $selected
     * @param array $selectAttributes
     * @param array $optionsAttributes
     * @param array $optgroupsAttributes
     *
     * @return HtmlString|string
     */
    public function select(
        string      $name,
        array|Collection       $list = [],
        bool|string|array|Collection|null $selected = null,
        array       $selectAttributes = [],
        array       $optionsAttributes = [],
        array       $optgroupsAttributes = []
    ): HtmlString|string
    {
        $this->type = 'select';

        /**
         * When building a select box the "value" attribute is really the selected one,
         * so we will use that when checking the model or session for a value which
         * should provide a convenient method of re-populating the forms on post.
         **/
        $selected = $this->getValueAttribute($name, $selected);

        $selectAttributes['id'] = $this->getIdAttribute($name, $selectAttributes);

        if (! isset($selectAttributes['name'])) {
            $selectAttributes['name'] = $name;
        }

        // We will simply loop through the options and build an HTML value for each of
        // them until we have an array of HTML declarations. Then we will join them
        // all together into one single HTML element that can be put on the form.
        $html = [];

        if (isset($selectAttributes['placeholder'])) {
            $html[] = $this->placeholderOption($selectAttributes['placeholder'], $selected);
            unset($selectAttributes['placeholder']);
        }

        foreach ($list as $value => $display) {
            $optionAttributes = $optionsAttributes[$value] ?? [];
            $optgroupAttributes = $optgroupsAttributes[$value] ?? [];
            $html[] = $this->getSelectOption($display, $value, $selected, $optionAttributes, $optgroupAttributes);
        }

        // Once we have all of this HTML, we can join this into a single element after
        // formatting the attributes into an HTML "attributes" string, then we will
        // build out a final select statement, which will contain all the values.
        $selectAttributes = $this->html->attributes($selectAttributes, 'select');

        $list = implode('', $html);

        return $this->toHtmlString("<select" . $selectAttributes . ">" . $list . "</select>");
    }

    /**
     * Create a select range field.
     *
     * @param string $name
     * @param string $begin
     * @param string $end
     * @param string|null $selected
     * @param array $options
     *
     * @return HtmlString|string
     */
    public function selectRange(string $name, string $begin, string $end, string|null $selected = null, array $options = []): HtmlString|string
    {
        $range = array_combine($range = range($begin, $end), $range);

        return $this->select($name, $range, $selected, $options);
    }

    /**
     * Create a select year field.
     *
     * @param string $name
     * @param string $begin
     * @param string $end
     * @param string|null $selected
     * @param array|null $options
     *
     * @return mixed
     */
    public function selectYear(string $name, string $begin, string $end, string|null $selected = null, array|null $options = null): mixed
    {
        return call_user_func_array([$this, 'selectRange'], func_get_args());
    }

    /**
     * Create a select month field.
     *
     * @param string $name
     * @param string|null $selected
     * @param array $options
     * @param string $format
     *
     * @return HtmlString|string
     */
    public function selectMonth(string $name, string|null $selected = null, array $options = [], string $format = 'F'): HtmlString|string
    {
        $months = [];

        foreach (range(1, 12) as $month) {
            $months[$month] = date($format, mktime(0, 0, 0, $month, 1));
        }

        return $this->select($name, $months, $selected, $options);
    }

    /**
     * Get the select option for the given value.
     *
     * @param string|array|Collection $display
     * @param string $value
     * @param string|array|Collection|null $selected
     * @param array $attributes
     * @param array $optgroupAttributes
     *
     * @return HtmlString|string
     */
    public function getSelectOption(string|array|Collection $display, string|int $value, string|array|Collection|null $selected, array $attributes = [], array $optgroupAttributes = []): HtmlString|string
    {
        if (is_iterable($display)) {
            return $this->optionGroup($display, $value, $selected, $optgroupAttributes, $attributes);
        }

        return $this->option($display, $value, $selected, $attributes);
    }

    /**
     * Create an option group form element.
     *
     * @param array $list
     * @param string $label
     * @param string|array|Collection|null $selected
     * @param array $attributes
     * @param array $optionsAttributes
     * @param integer $level
     *
     * @return string
     */
    protected function optionGroup(array|Collection $list, string $label, string|array|Collection|null $selected, array $attributes = [], array $optionsAttributes = [], int $level = 0): HtmlString|string
    {
        $html = [];
        $space = str_repeat("&nbsp;", $level);
        foreach ($list as $value => $display) {
            $optionAttributes = $optionsAttributes[$value] ?? [];
            if (is_iterable($display)) {
                $html[] = $this->optionGroup($display, $value, $selected, $attributes, $optionAttributes, $level+5);
            } else {
                $html[] = $this->option($space.$display, $value, $selected, $optionAttributes);
            }
        }
        return $this->toHtmlString('<optgroup label="' . e($space.$label, false) . '"' . $this->html->attributes($attributes, 'optionGroup') . '>' . implode('', $html) . '</optgroup>');
    }

    /**
     * Create a select element option.
     *
     * @param string|null $display
     * @param string $value
     * @param string|array|Collection|null $selected
     * @param array $attributes
     *
     * @return HtmlString|string
     */
    protected function option(string|null $display, string|int $value, string|array|Collection|null $selected = null, array $attributes = []): HtmlString|string
    {
        $selected = $this->getSelectedValue($value, $selected);

        $options = array_merge(['value' => $value, 'selected' => $selected], $attributes);

        $string = '<option' . $this->html->attributes($options, 'option') . '>';
        if ($display !== null) {
            $string .= e($display, false) . '</option>';
        }

        return $this->toHtmlString($string);
    }

    /**
     * Create a placeholder select element option.
     *
     * @param $display
     * @param $selected
     *
     * @return HtmlString|string
     */
    protected function placeholderOption($display, $selected): HtmlString|string
    {
        $selected = $this->getSelectedValue(null, $selected);

        $options = [
            'selected' => $selected,
            'value' => '',
        ];

        return $this->toHtmlString('<option' . $this->html->attributes($options, 'placeholder') . '>' . e($display, false) . '</option>');
    }

    /**
     * Determine if the value is selected.
     *
     * @param string|int|bool $value
     * @param string|array|Collection|null $selected
     *
     * @return bool|string|null
     */
    protected function getSelectedValue(string|int|bool|null $value, string|array|Collection|null $selected): bool|string|null
    {
        if (is_array($selected)) {
            return in_array($value, $selected, true) || in_array((string) $value, $selected, true) ? 'selected' : null;
        } elseif ($selected instanceof Collection) {
            return $selected->contains($value) ? 'selected' : null;
        }
        if (is_int($value) && is_bool($selected)) {
            return (bool)$value === $selected;
        }
        return ((string) $value === (string) $selected) ? 'selected' : null;
    }

    /**
     * Create a checkbox input field.
     *
     * @param string $name
     * @param mixed $value
     * @param bool|null $checked
     * @param array $options
     *
     * @return HtmlString|string
     */
    public function checkbox(string $name, mixed $value = 1, bool|null $checked = null, array $options = []): HtmlString|string
    {
        return $this->checkable('checkbox', $name, $value, $checked, $options);
    }

    /**
     * Create a radio button input field.
     *
     * @param string $name
     * @param mixed|null $value
     * @param bool|null $checked
     * @param array $options
     *
     * @return HtmlString|string
     */
    public function radio(string $name, mixed $value = null, bool|null $checked = null, array $options = []): HtmlString|string
    {
        if (is_null($value)) {
            $value = $name;
        }

        return $this->checkable('radio', $name, $value, $checked, $options);
    }

    /**
     * Create a checkable input field.
     *
     * @param string $type
     * @param string $name
     * @param mixed $value
     * @param bool|null $checked
     * @param array $options
     *
     * @return HtmlString|string
     */
    protected function checkable(string $type, string $name, mixed $value, bool|null $checked, array $options): HtmlString|string
    {
        $this->type = $type;

        $checked = $this->getCheckedState($type, $name, $value, $checked);

        if ($checked) {
            $options['checked'] = 'checked';
        }

        return $this->input($type, $name, $value, $options);
    }

    /**
     * Get the check state for a checkable input.
     *
     * @param string $type
     * @param string $name
     * @param  mixed  $value
     * @param bool|null $checked
     *
     * @return bool
     */
    protected function getCheckedState(string $type, string $name, mixed $value, bool|null $checked): bool
    {
        return match ($type) {
            'checkbox' => $this->getCheckboxCheckedState($name, $value, $checked),
            'radio' => $this->getRadioCheckedState($name, $value, $checked),
            default => $this->compareValues($name, $value),
        };
    }

    /**
     * Get the check state for a checkbox input.
     *
     * @param string $name
     * @param  mixed  $value
     * @param bool|null $checked
     *
     * @return bool
     */
    protected function getCheckboxCheckedState(string $name, mixed $value, bool|null $checked): bool
    {
        $request = $this->request($name);

        if (isset($this->session) && ! $this->oldInputIsEmpty() && is_null($this->old($name)) && !$request) {
            return false;
        }

        if ($this->missingOldAndModel($name) && is_null($request)) {
            return (bool) $checked;
        }

        $posted = $this->getValueAttribute($name, $checked);

        if (is_array($posted)) {
            return in_array($value, $posted);
        } elseif ($posted instanceof Collection) {
            return $posted->contains('id', $value);
        } else {
            return (bool) $posted;
        }
    }

    /**
     * Get the check state for a radio input.
     *
     * @param string $name
     * @param  mixed  $value
     * @param bool $checked
     *
     * @return bool
     */
    protected function getRadioCheckedState(string $name, mixed $value, bool|null $checked): bool
    {
        $request = $this->request($name);

        if ($this->missingOldAndModel($name) && !$request) {
            return (bool) $checked;
        }

        return $this->compareValues($name, $value);
    }

    /**
     * Determine if the provide value loosely compares to the value assigned to the field.
     * Use loose comparison because Laravel model casting may be in effect and therefore
     * 1 == true and 0 == false.
     *
     * @param string $name
     * @param string $value
     * @return bool
     */
    protected function compareValues(string $name, string $value): bool
    {
        return $this->getValueAttribute($name) == $value;
    }

    /**
     * Determine if old input or model input exists for a key.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function missingOldAndModel(string $name): bool
    {
        return (is_null($this->old($name)) && is_null($this->getModelValueAttribute($name)));
    }

    /**
     * Create a HTML reset input element.
     *
     * @param string $value
     * @param array $attributes
     *
     * @return HtmlString|string
     */
    public function reset(string $value, array $attributes = []): HtmlString|string
    {
        return $this->input('reset', null, $value, $attributes);
    }

    /**
     * Create a HTML image input element.
     *
     * @param string $url
     * @param string|null $name
     * @param array $attributes
     *
     * @return HtmlString|string
     */
    public function image(string $url, string|null $name = null, array $attributes = []): HtmlString|string
    {
        $attributes['src'] = $this->url->asset($url);

        return $this->input('image', $name, null, $attributes);
    }

    /**
     * Create a month input field.
     *
     * @param string $name
     * @param string|DateTimeInterface|null $value
     * @param array $options
     *
     * @return HtmlString|string
     */
    public function month(string $name, string|DateTimeInterface|null $value = null, array $options = []): HtmlString|string
    {
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m');
        }

        return $this->input('month', $name, $value, $options);
    }

    /**
     * Create a color input field.
     *
     * @param string $name
     * @param string|null $value
     * @param array $options
     *
     * @return HtmlString|string
     */
    public function color(string $name, string|null $value = null, array $options = []): HtmlString|string
    {
        return $this->input('color', $name, $value, $options);
    }

    /**
     * Create a submit button element.
     *
     * @param string|null $value
     * @param array $options
     *
     * @return HtmlString|string
     */
    public function submit(string|null $value = null, array $options = []): HtmlString|string
    {
        return $this->input('submit', null, $value, $options);
    }

    /**
     * Create a button element.
     *
     * @param string|null $value
     * @param array $options
     *
     * @return HtmlString|string
     */
    public function button(string|null $value = null, array $options = []): HtmlString|string
    {
        if (! array_key_exists('type', $options)) {
            $options['type'] = 'button';
        }

        return $this->toHtmlString('<button' . $this->html->attributes($options, 'button') . '>' . $value . '</button>');
    }

    /**
     * Create a datalist box field.
     *
     * @param string $id
     * @param array $list
     *
     * @return HtmlString|string
     */
    public function datalist(string $id, array $list = []): HtmlString|string
    {
        $this->type = 'datalist';

        $attributes['id'] = $id;

        $html = [];

        if ($this->isAssociativeArray($list)) {
            foreach ($list as $value => $display) {
                $html[] = $this->option($display, $value, null);
            }
        } else {
            foreach ($list as $value) {
                $html[] = $this->option($value, $value, null);
            }
        }

        $attributes = $this->html->attributes($attributes, 'datalist');

        $list = implode('', $html);

        return $this->toHtmlString("<datalist" . $attributes . ">" . $list . "</datalist>");
    }

    /**
     * Determine if an array is associative.
     *
     * @param array $array
     * @return bool
     */
    protected function isAssociativeArray(array $array): bool
    {
        return (array_values($array) !== $array);
    }

    /**
     * Parse the form action method.
     *
     * @param string $method
     *
     * @return string
     */
    protected function getMethod(string $method): string
    {
        $method = strtoupper($method);

        return $method !== 'GET' ? 'POST' : $method;
    }

    /**
     * Get the form action from the options.
     *
     * @param  array $options
     *
     * @return string
     */
    protected function getAction(array $options): string
    {
        // We will also check for a "route" or "action" parameter on the array so that
        // developers can easily specify a route or controller action when creating
        // a form providing a convenient interface for creating the form actions.
        if (isset($options['url'])) {
            return $this->getUrlAction($options['url']);
        }

        if (isset($options['route'])) {
            return $this->getRouteAction($options['route']);
        }

        // If an action is available, we are attempting to open a form to a controller
        // action route. So, we will use the URL generator to get the path to these
        // actions and return them from the method. Otherwise, we'll use current.
        elseif (isset($options['action'])) {
            return $this->getControllerAction($options['action']);
        }

        return $this->url->current();
    }

    /**
     * Get the action for "url" option.
     *
     * @param array|string $options
     *
     * @return string
     */
    protected function getUrlAction(array|string $options): string
    {
        if (is_array($options)) {
            return $this->url->to($options[0], array_slice($options, 1));
        }

        return $this->url->to($options);
    }

    /**
     * Get the action for a "route" option.
     *
     * @param array|string $options
     *
     * @return string
     */
    protected function getRouteAction(array|string $options): string
    {
        if (is_array($options)) {
            $parameters = array_slice($options, 1);

            if (array_keys($options) === [0, 1]) {
                $parameters = head($parameters);
            }

            return $this->url->route($options[0], $parameters);
        }

        return $this->url->route($options);
    }

    /**
     * Get the action for an "action" option.
     *
     * @param array|string $options
     *
     * @return string
     */
    protected function getControllerAction(array|string $options): string
    {
        if (is_array($options)) {
            return $this->url->action($options[0], array_slice($options, 1));
        }

        return $this->url->action($options);
    }

    /**
     * Get the form appendage for the given method.
     *
     * @param string $method
     * @param array|null $method_attributes
     * @return string
     */
    protected function getAppendage(string $method, array|null $method_attributes = null): string
    {
        list($method, $appendage) = [strtoupper($method), ''];

        // If the HTTP method is in this list of spoofed methods, we will attach th
        // method spoofer hidden input to the form. This allows us to use regular
        // form to initiate PUT and DELETE requests in addition to the typical.
        if (in_array($method, $this->spoofedMethods)) {
            $appendage .= $this->hidden('_method', $method, $method_attributes ?? []);
        }

        // If the method is something other than GET we will go ahead and attach the
        // CSRF token to the form, as this can't hurt and is convenient to simply
        // always have available on every form the developers creates for them.
        // Check injectCsrfToken to see if developer has explicitly disabled
        if ($method !== 'GET' && $this->injectCsrfToken) {
            $appendage .= $this->token();
        }

        // If we create more than one form on the same page, the injectCsrfToken property
        // will remain the same across every form since the form builder is resolved once.
        // In order to ensure the next form will start with a csrf_token by default, we
        // need to set it to true.
        $this->injectCsrfToken = true;

        return $appendage;
    }

    /**
     * Get the ID attribute for a field name.
     *
     * @param string|null $name
     * @param array $attributes
     *
     * @return string
     */
    public function getIdAttribute(string|null $name, array $attributes): string
    {
        if (array_key_exists('id', $attributes)) {
            return $attributes['id'];
        }

        if (in_array($name, $this->labels)) {
            return $name;
        }
        return '';
    }

    /**
     * Get the value that should be assigned to the field.
     *
     * @param string|null $name
     * @param string|array|null $value
     *
     * @return mixed
     */
    public function getValueAttribute(string|null $name, string|null|array $value = null): mixed
    {
        if (is_null($name)) {
            return $value;
        }

        $old = $this->old($name);

        if (! is_null($old) && $name !== '_method') {
            return $old;
        }
        if (function_exists('app') && !empty(app('Illuminate\Routing\Router')->current())) {
            $hasNullMiddleware = in_array(
                    'Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull',
                    app('Illuminate\Routing\Router')->gatherRouteMiddleware(app('Illuminate\Routing\Router')->current())
                );

            if ($hasNullMiddleware
                && is_null($old)
                && is_null($value)
                && !is_null($this->view->shared('errors'))
                && count(is_countable($this->view->shared('errors')) ? $this->view->shared('errors') : []) > 0
            ) {
                return null;
            }
        }
        $request = $this->request($name);
        if (! is_null($request) && $name != '_method') {
            return $request;
        }

        if (! is_null($value)) {
            return $value;
        }

        if (isset($this->model)) {
            return $this->getModelValueAttribute($name);
        }
        return '';
    }

    /**
     * Take Request in fill process
     * @param bool $consider
     */
    public function considerRequest(bool $consider = true): void
    {
        $this->considerRequest = $consider;
    }

    /**
     * Get value from current Request
     * @param $name
     * @return array|null|string
     */
    protected function request($name): array|string|null
    {
        if (!$this->considerRequest) {
            return null;
        }

        if (!isset($this->request)) {
            return null;
        }

        return $this->request->input($this->transformKey($name));
    }

    /**
     * Get the model value that should be assigned to the field.
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function getModelValueAttribute(string $name): mixed
    {
        $key = $this->transformKey($name);

        if ((is_string($this->model) || is_object($this->model)) && method_exists($this->model, 'getFormValue')) {
            return $this->model->getFormValue($key);
        }

        return data_get($this->model, $key);
    }

    /**
     * Get a value from the session's old input.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function old(string $name): mixed
    {
        if (!isset($this->session)) {
            return null;
        }
        $key = $this->transformKey($name);
        $payload = $this->session->getOldInput($key);

        if (!is_array($payload)) {
            return $payload;
        }

        if (!in_array($this->type, ['select', 'checkbox'])) {
            if (!isset($this->payload[$key])) {
                $this->payload[$key] = collect($payload);
            }

            if (!empty($this->payload[$key])) {
                return $this->payload[$key]->shift();
            }
        }

        return $payload;
    }

    /**
     * Determine if the old input is empty.
     *
     * @return bool
     */
    public function oldInputIsEmpty(): bool
    {
        return (isset($this->session) && count((array) $this->session->getOldInput()) === 0);
    }

    /**
     * Transform key from array to dot syntax.
     *
     * @param string $key
     *
     * @return string|array
     */
    protected function transformKey(string $key): string|array
    {
        return str_replace(['.', '[]', '[', ']'], ['_', '', '.', ''], $key);
    }

    /**
     * Transform the string to a Html serializable object
     *
     * @param $html
     *
     * @return HtmlString
     */
    protected function toHtmlString($html): HtmlString
    {
        return new HtmlString($html);
    }

    /**
     * Get the session store implementation.
     *
     * @return  Session  $session
     */
    public function getSessionStore(): Session
    {
        return $this->session;
    }

    /**
     * Set the session store implementation.
     *
     * @param Session $session
     *
     * @return $this
     */
    public function setSessionStore(Session $session): static
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return View|mixed
     *
     * @throws BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (static::hasComponent($method)) {
            return $this->componentCall($method, $parameters);
        }

        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        throw new BadMethodCallException("Method " . $method . " does not exist.");
    }
}
