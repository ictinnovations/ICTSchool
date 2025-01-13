<?php /** @noinspection PhpUnused */

namespace LaravelLux\Html;

use BadMethodCallException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Routing\UrlGenerator;

class HtmlBuilder
{
    use Macroable, Componentable {
        Macroable::__call as macroCall;
        Componentable::__call as componentCall;
    }

    /**
     * The URL generator instance.
     *
     * @var UrlGenerator
     */
    protected UrlGenerator $url;

    /**
     * The View Factory instance.
     *
     * @var Factory
     */
    protected Factory $view;

    /**
     * Create a new HTML builder instance.
     *
     * @param UrlGenerator $url
     * @param Factory $view
     */
    public function __construct(UrlGenerator $url, Factory $view)
    {
        $this->url = $url;
        $this->view = $view;
    }

    /**
     * Convert an HTML string to entities.
     *
     * @param string $value
     *
     * @return string
     */
    public function entities(string $value): string
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Convert entities to HTML characters.
     *
     * @param string $value
     *
     * @return string
     */
    public function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate a link to a JavaScript file.
     *
     * @param string $url
     * @param array $attributes
     * @param bool|null $secure
     *
     * @return HtmlString|string
     */
    public function script(string $url, array $attributes = [], bool|null $secure = null): HtmlString|string
    {
        $attributes['src'] = $this->url->asset($url, $secure);

        return $this->toHtmlString('<script' . $this->attributes($attributes) . '></script>');
    }

    /**
     * Generate a link to a CSS file.
     *
     * @param string $url
     * @param array $attributes
     * @param bool|null $secure
     *
     * @return HtmlString|string
     */
    public function style(string $url, array $attributes = [], bool|null $secure = null): HtmlString|string
    {
        $defaults = ['media' => 'all', 'type' => 'text/css', 'rel' => 'stylesheet'];

        $attributes = array_merge($defaults, $attributes);

        $attributes['href'] = $this->url->asset($url, $secure);

        return $this->toHtmlString('<link' . $this->attributes($attributes) . '>');
    }

    /**
     * Generate an HTML image element.
     *
     * @param string $url
     * @param string|null $alt
     * @param array $attributes
     * @param bool|null $secure
     *
     * @return HtmlString|string
     */
    public function image(string $url, string|null $alt = null, array $attributes = [], bool|null $secure = null): HtmlString|string
    {
        $attributes['alt'] = $alt;

        return $this->toHtmlString('<img src="' . $this->url->asset($url,
            $secure) . '"' . $this->attributes($attributes) . '>');
    }

    /**
     * Generate a link to a Favicon file.
     *
     * @param string $url
     * @param array $attributes
     * @param bool|null $secure
     *
     * @return HtmlString|string
     */
    public function favicon(string $url, array $attributes = [], bool|null $secure = null): HtmlString|string
    {
        $defaults = ['rel' => 'shortcut icon', 'type' => 'image/x-icon'];

        $attributes = array_merge($defaults, $attributes);

        $attributes['href'] = $this->url->asset($url, $secure);

        return $this->toHtmlString('<link' . $this->attributes($attributes) . '>');
    }

    /**
     * Generate a HTML link.
     *
     * @param string $url
     * @param string|null $title
     * @param array $attributes
     * @param bool|null $secure
     * @param bool $escape
     *
     * @return HtmlString|string
     */
    public function link(string $url, string|null $title = null, array $attributes = [], bool|null $secure = null, bool $escape = true): HtmlString|string
    {
        $url = $this->url->to($url, [], $secure);

        if (!$title) {
            $title = $url;
        }

        if ($escape) {
            $title = $this->entities($title);
        }

        return $this->toHtmlString('<a href="' . $this->entities($url) . '"' . $this->attributes($attributes) . '>' . $title . '</a>');
    }

    /**
     * Generate a HTTPS HTML link.
     *
     * @param string $url
     * @param string|null $title
     * @param array $attributes
     * @param bool $escape
     *
     * @return HtmlString|string
     */
    public function secureLink(string $url, string|null $title = null, array $attributes = [], bool $escape = true): HtmlString|string
    {
        return $this->link($url, $title, $attributes, true, $escape);
    }

    /**
     * Generate HTML link to an asset.
     *
     * @param string $url
     * @param string|null $title
     * @param array $attributes
     * @param bool|null $secure
     * @param bool $escape
     *
     * @return HtmlString|string
     */
    public function linkAsset(string $url, string|null $title = null, array $attributes = [], bool|null $secure = null, bool $escape = true): HtmlString|string
    {
        $url = $this->url->asset($url, $secure);

        return $this->link($url, $title ?: $url, $attributes, $secure, $escape);
    }

    /**
     * Generate HTTPS HTML link to an asset.
     *
     * @param string $url
     * @param string|null $title
     * @param array $attributes
     * @param bool $escape
     *
     * @return HtmlString|string
     */
    public function linkSecureAsset(string $url, string|null $title = null, array $attributes = [], bool $escape = true): HtmlString|string
    {
        return $this->linkAsset($url, $title, $attributes, true, $escape);
    }

    /**
     * Generate HTML link to a named route.
     *
     * @param string $name
     * @param string|null $title
     * @param mixed $parameters
     * @param array $attributes
     * @param bool|null $secure
     * @param bool $escape
     *
     * @return HtmlString|string
     */
    public function linkRoute(string $name, string|null $title = null, mixed $parameters = [], array $attributes = [], bool|null $secure = null, bool $escape = true): HtmlString|string
    {
        return $this->link($this->url->route($name, $parameters), $title, $attributes, $secure, $escape);
    }

    /**
     * Generate a HTML link to a controller action.
     *
     * @param string $action
     * @param string|null $title
     * @param array $parameters
     * @param array $attributes
     * @param bool|null $secure
     * @param bool $escape
     *
     * @return HtmlString|string
     */
    public function linkAction(string $action, string|null $title = null, array $parameters = [], array $attributes = [], bool|null $secure = null, bool $escape = true): HtmlString|string
    {
        return $this->link($this->url->action($action, $parameters), $title, $attributes, $secure, $escape);
    }

    /**
     * Generate HTML link to an email address.
     *
     * @param string $email
     * @param string|null $title
     * @param array $attributes
     * @param bool $escape
     *
     * @return HtmlString|string
     */
    public function mailto(string $email, string|null $title = null, array $attributes = [], bool $escape = true): HtmlString|string
    {
        $email = $this->email($email);

        $title = $title ?: $email;

        if ($escape) {
            $title = $this->entities($title);
        }

        $email = $this->obfuscate('mailto:') . $email;

        return $this->toHtmlString('<a href="' . $email . '"' . $this->attributes($attributes) . '>' . $title . '</a>');
    }

    /**
     * Obfuscate an e-mail address to prevent spam bots from sniffing it.
     *
     * @param string $email
     *
     * @return string
     */
    public function email(string $email): string
    {
        return str_replace('@', '&#64;', $this->obfuscate($email));
    }

    /**
     * Generates non-breaking space entities based on number supplied.
     *
     * @param int $num
     *
     * @return string
     */
    public function nbsp(int $num = 1): string
    {
        return str_repeat('&nbsp;', $num);
    }

    /**
     * Generate an ordered list of items.
     *
     * @param array $list
     * @param array $attributes
     *
     * @return HtmlString|string
     */
    public function ol(array $list, array $attributes = []): HtmlString|string
    {
        return $this->listing('ol', $list, $attributes);
    }

    /**
     * Generate an un-ordered list of items.
     *
     * @param array $list
     * @param array $attributes
     *
     * @return HtmlString|string
     */
    public function ul(array $list, array $attributes = []): HtmlString|string
    {
        return $this->listing('ul', $list, $attributes);
    }

    /**
     * Generate a description list of items.
     *
     * @param array $list
     * @param array $attributes
     *
     * @return HtmlString|string
     */
    public function dl(array $list, array $attributes = []): HtmlString|string
    {
        $attributes = $this->attributes($attributes);

        $html = "<dl" . $attributes . ">";

        foreach ($list as $key => $value) {
            $value = (array) $value;

            $html .= "<dt>$key</dt>";

            foreach ($value as $v_value) {
                $html .= "<dd>$v_value</dd>";
            }
        }

        $html .= '</dl>';

        return $this->toHtmlString($html);
    }

    /**
     * Create a listing HTML element.
     *
     * @param string $type
     * @param array $list
     * @param array $attributes
     *
     * @return HtmlString|string
     */
    protected function listing(string $type, array $list, array $attributes = []): HtmlString|string
    {
        $html = '';

        if (count($list) === 0) {
            return $html;
        }

        // Essentially we will just spin through the list and build the list of the HTML
        // elements from the array. We will also handle nested lists in case that is
        // present in the array. Then we will build out the final listing elements.
        foreach ($list as $key => $value) {
            $html .= $this->listingElement($key, $type, $value);
        }

        $attributes = $this->attributes($attributes);

        return $this->toHtmlString("<" . $type . $attributes . ">" . $html . "</" . $type . ">");
    }

    /**
     * Create the HTML for a listing element.
     *
     * @param mixed $key
     * @param string $type
     * @param mixed $value
     *
     * @return string
     */
    protected function listingElement(mixed $key, string $type, mixed $value): string
    {
        if (is_array($value)) {
            return $this->nestedListing($key, $type, $value);
        } else {
            return '<li>' . e($value, false) . '</li>';
        }
    }

    /**
     * Create the HTML for a nested listing attribute.
     *
     * @param mixed $key
     * @param string $type
     * @param mixed $value
     *
     * @return string
     */
    protected function nestedListing(mixed $key, string $type, mixed $value): string
    {
        if (is_int($key)) {
            return $this->listing($type, $value);
        } else {
            return '<li>' . $key . $this->listing($type, $value) . '</li>';
        }
    }

    /**
     * Build an HTML attribute string from an array.
     *
     * @param array|null $attributes
     * @param string|null $input_type
     * @return string
     */
    public function attributes(array|null $attributes, string|null $input_type = null): string
    {
        $defaultAttributes = config('html-forms.default_attributes', []);
        $all_default_attributes = $defaultAttributes['all'] ?? [];
        $specific_attributes = $defaultAttributes[$input_type] ?? [];

        foreach([$all_default_attributes, $specific_attributes] as $mergeable_attributes) {
            foreach ($mergeable_attributes as $attribute_name => $attribute_value){
                if(empty($attribute_value)){
                    continue;
                }
                if ($attribute_name === "class") {
                    $attributes['class'] = [...$attribute_value, ...$attributes['class'] ?? []];
                } else {
                    $attributes[$attribute_name] = is_array($attribute_value) && !$attribute_value[0]
                        ? array_key_first($attribute_value)
                        : $attribute_value;
                }
            }
        }

        if (empty($attributes)) {
            return '';
        }

        $ignoreEmptyAttributes = config('html-forms.ignore_empty_attributes', []);
        $ignoreEmptyAll = $ignoreEmptyAttributes['all'] ?? [];
        $ignoreEmptySpecific = $ignoreEmptyAttributes[$input_type] ?? [];
        $allowedBooleanValues = config('html-forms.allowed_boolean_values', []);
        $ignoreAllEmptyAttributes = config('html-forms.ignore_all_empty_attributes', []);

        $html = [];
        $ignoreables = array_merge($ignoreEmptyAll, $ignoreEmptySpecific);
        $allow_boolean = in_array($input_type, $allowedBooleanValues);

        foreach ($attributes as $key => $value) {
            $ignore_empty = $ignoreAllEmptyAttributes || in_array($key, $ignoreables);
            $element = $this->attributeElement($key, $value, $ignore_empty, $allow_boolean);

            if (!empty($element)) {
                $html[] = $element;
            }
        }

        return empty($html) ? '' : ' ' . implode(' ', $html);
    }

    /**
     * Build a single attribute element.
     *
     * @param string|int $key
     * @param string|bool|array|null $value
     * @param bool $ignore_empty
     * @param bool $allow_boolean
     * @return string|null
     */
    protected function attributeElement(string|int $key, mixed $value, bool $ignore_empty = false, bool $allow_boolean = false): null|string
    {
        // For numeric keys we will assume that the value is a boolean attribute
        // where the presence of the attribute represents a true value and the
        // absence represents a false value.
        // This will convert HTML attributes such as "required" to a correct
        // form instead of using incorrect numerics.
        if (is_numeric($key)) {
            return $value;
        }

        // Treat boolean attributes as HTML properties
        if (is_bool($value) && ($key !== 'value' || $allow_boolean)) {
            return $value ? $key : '';
        }

        if (is_array($value) && $key === 'class') {
            return 'class="' . implode(' ', $value) . '"';
        }

        if($ignore_empty || !empty($value) || ($allow_boolean && $value == "0")){
            return $key . '="' . e($value ?? '', false) . '"';
        }
        return null;
    }

    /**
     * Obfuscate a string to prevent spam-bots from sniffing it.
     *
     * @param string $value
     *
     * @return string
     */
    public function obfuscate(string $value): string
    {
        $safe = '';

        foreach (str_split($value) as $letter) {
            if (ord($letter) > 128) {
                return $letter;
            }

            // To properly obfuscate the value, we will randomly convert each letter to
            // its entity or hexadecimal representation, keeping a bot from sniffing
            // the randomly obfuscated letters out of the string on the responses.
            switch (rand(1, 3)) {
                case 1:
                    $safe .= '&#' . ord($letter) . ';';
                    break;

                case 2:
                    $safe .= '&#x' . dechex(ord($letter)) . ';';
                    break;

                case 3:
                    $safe .= $letter;
            }
        }

        return $safe;
    }

    /**
     * Generate a meta tag.
     *
     * @param string|null $name
     * @param string $content
     * @param array $attributes
     *
     * @return HtmlString|string
     */
    public function meta(string|null $name, string $content, array $attributes = []): HtmlString|string
    {
        $defaults = compact('name', 'content');

        $attributes = array_merge($defaults, $attributes);

        return $this->toHtmlString('<meta' . $this->attributes($attributes) . '>');
    }

    /**
     * Generate an html tag.
     *
     * @param string $tag
     * @param mixed $content
     * @param array $attributes
     *
     * @return HtmlString|string
     */
    public function tag(string $tag, mixed $content, array $attributes = []): HtmlString|string
    {
        $content = is_array($content) ? implode('', $content) : $content;
        return $this->toHtmlString('<' . $tag . $this->attributes($attributes) . '>' . $this->toHtmlString($content) . '</' . $tag . '>');
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
