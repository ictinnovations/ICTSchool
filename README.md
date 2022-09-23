
School management system feature list
====================================

#### Features
-------------
* __Multi-Branches System__
* __Accounts Management__
* __Result & Report Management__
* __Mobile Based Attendance System__
* __Exam and Paper Management__
* __Fee Management__
* __Class and Section Management__
* __Student Management and admission__
* __SMS Notification  System__
* __Voice Notification System__


#### Student admission system 
-----------------------------
* __Fully functional and automated admission form for student enrolment__

* __Enroll students to a specific class and section for a certain session__

#### Class-wise subject management  
----------------------------------
* __Add subjects for each class separately__

#### Student promotion 
-----------------------
* __promote a student from one class to another__

#### Students’ daily attendance  
-------------------------------
* __Take attendance of students daily__
* __Keep track if students are absent__


#### Students’ attendance report  
---------------------------------
* __Get a well-defined attendance report for all students of a certain class for a certain month__

#### Exam evaluations or marks management   
------------------------------------------
* __Evaluate or put exam marks for each student subject wise__
* __Compare students’ marks__
* __Print student mark sheet__

#### Students’ fees management   
-------------------------------
* __Create invoice for student fees__
* __Automatically send fees notification to specific date whose set in amdin__

#### Academic year or session handling    
---------------------------------------
* __Keep your school records year-wise__
* __Ability to select academic sessions__
* __Ability to see previous session data__

#### Management of teachers     
----------------------------
* __Add/edit/delete teachers anytime you need__
* __Assign teacher to a specific class or section__
* __Assign teacher to specific subject__

#### Customization of school information    
-----------------------------------------
* __Change school name and other information from system settings__

#### Teacher Panel    
-------------------
* __teacher show dashboard attendance and teachers portion__

## ICTSchool Installation


** First git clone
```
 https://github.com/ictinnovations/ICTSchool.git
 ```
#### Create ENV file
to create env file run, navigate to ICTSchool direcotry and run the following command.
```
cp .env.example .env
```
#### Database creds config in ENV

After create env file, enter database creds e.g.
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=schoolapi
DB_USERNAME=root
DB_PASSWORD=
```

#### Create Database
After change configuration according to your need, create same name database which is defined in **env** file.

### Run Composer Command
After all configuration, we need to run **composer** command to install laravel and other packages. Run the following command
```
composer install
```
>After installing Laravel, you may need to configure some permissions. Directories within the storage and the bootstrap/cache directories should be writable by your web server or Laravel will not run.

After run **composer install**  run the following command to genrate **APP_KEY**, It is a private string (encryption_key) in your application that nobody knows about. So, if only your application knows the key, only your application can decrypt data that is encrypted by this key.
```
php artisan key:generate
```
### Database Migration
Migrations are like version control for your database, allowing your team to easily modify and share the application's database schema. Migrations are typically paired with Laravel's schema builder to easily build your application's database schema. If you have ever had to tell a teammate to manually add a column to their local database schema, you've faced the problem that database migrations solve.
```
$ php artisan migrate
```
```
$ php artisan db:seed
```
```
$ php artisan passport:install (for restfull Api)
```
```
$ php artisan storage:link
```
```
$ php artisan serve --port 8080
```
**  http://localhost:8080 **
```
$ php artisan storage:link

```
## cron job Settings
```
crontab -e

* * * * * /usr/bin/php7.1 /path/artisan schedule:run 1>> /dev/null 2>&1

```
# Screenshot
============

<img src="screenshoot/Screenshot(21).png" >
<img src="screenshoot/Screenshot(22).png" >
<img src="screenshoot/Screenshot(23).png" >
<img src="screenshoot/Screenshot(24).png" >
<img src="screenshoot/Screenshot(25).png" >
<img src="screenshoot/Screenshot(26).png" >
<img src="screenshoot/Screenshot(27).png" >
<img src="screenshoot/Screenshot(28).png" >
<img src="screenshoot/Screenshot(29).png" >
<img src="screenshoot/Screenshot(30).png" >
<img src="screenshoot/Screenshot(31).png" >
<img src="screenshoot/Screenshot(32).png" >
<img src="screenshoot/Screenshot(34).png" >
<img src="screenshoot/Screenshot(35).png" >
<img src="screenshoot/Screenshot(33).png" >

System Dependencies
===================
- PHP version 7.2.33
- Composer version 1.10.1
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

[ICTSchool](https://github.com/ictinnovations/ICTSchool) has been developed by [ICT Innovations](https://www.ictinnovations.com/) 

