FORMAT: 1A
HOST: http://demo.ictschool.com/api/


# ICT School System Apis

# Data Structures

## User (object)
+ id: 1 (number)
+ firstname: adeel (string) 
+ lastname: sharif (string)
+ phone: 03001234567 (number)
+ desc: Description (string)
+ login: username (string)
+ group: usertype like,Admin,Teacher and Student (string)



## RequestUser (object)

+ firstname: firstname (string , required) 
+ lastname: lastname (string , required)
+ phone: phone (string , required)
+ login: username (string , required)
+ password: password (string , required) 
+ group: usertype like,Admin,Teacher and Student (string , required)



## Teachers (object)
+ id: 1 (number,default)
+ firstname: firstname (string , required)
+ lastname: lastname (string , required)
+ gender: Male,Female (string, required)
+ dob: date of brith (string, required)
+ email: email (string, required)
+ phone: phone (string , required)
+ fathername: father name (string, required)
+ fathercellno: 03001234567 (number, required)
+ presentaddress: presentAddress (string, required)



## Teacherssection (object)

+ class: class name (string)
+ section: section name (string)



## Teacherssubjects (object)

+ subject: subject name (string)
+ class: class name (string)


## Messages (object)

+ filename: filename.wav (string ) - return wav file

## Classes (object)

+ id: 1 (number ,default)
+ code: class code (string,required)
+ name: class name (string,required)
+ description: class name (string,optional)



## sections (object)

+ id: 1 (number ,default)
+ name: section name (string,required)
+ description: description (string,required)
+ class_code: class code (string,required)



## Students (object)
+ id: 1 (number,default)
+ regiNo: regiNo (string , required) 
+ rollNo: rolNo (string , required)
+ firstname: firstname (string , required)
+ lastname: lastname (string , required)
+ gender: Male,Female (string, required)
+ dob: date of brith (string, required)
+ religion: religion (string , required)
+ fathername: father name (string, required)
+ fathercellno: 03001234567 (number, required)
+ mothername: Mother Name (string , required) 
+ mothercellno: 03001234567 (number , required)
+ class: class (string, required)
+ section: section (string, required)
+ group: group,sicence,art (string, required)
+ session: year (string , required)
+ presentaddress: presentAddress (string, required)


## Notifications (object)

+ name: notification name (string , required)
+ type: voice or sms  (string , required)
+ message: if voice send file name or if sms send message text (string , required)

## Exams (object)
+ id: 1 (number) - exam id auto incremented
+ type: class test,final exam,middle term,first term (string) - exam type
+ class: one,two etc (string ) - class name
+ section: A,B,C (string) - section name

## Results (object)

+ id: 1 (number) - result id
+ regiNo: 1234 (number) - registration number
+ subject: 001 (string) - subject code
+ rollNo: 02 (number) - rollNo
+ firstName: abc (string) - first name
+ lastName: def (string) - last name
+ class: one (string) - class name
+ section: A (string) - section name
+ written: 25 (number) - obtain written subject marks
+ mcq: 25 (number) - obtain mcq subject marks
+ practical: 25 (number) - obtain practical subject marks
+ ca: 25 (number) - obtain ca subject marks
+ total: 100 (number) - obtain total subject marks
+ grade: A (string) - pre define grade
+ point: 4 (number) - pre define point
+ Absent: No (string) - Yes,NO

## Resultsrequest (object)

+ class_id: 1 (number) - class id 
+ section_id: 1 (number) -section id 
+ session: 2018 (number) - year
+ regiNo: 222 (number) - student registration number
+ exam_id: 1 (number) - exam id 
+ subject_code: subject code (string)
+ written: 25 (number) - obtain marks
+ mcq: 25 (number) - obtain marks
+ practical:25 (number) - obtain marks
+ ca: 25 (number) - obtain marks
+ absent: NO (string) - Yes , NO

## Attendances (object)

+ id: 1 (number) -  attendance id
+ regiNo: 170101 (number) - student registration number
+ rollNo: 01 (number) - student roll number
+ firstName: abc (string) - student first name
+ middleName: def (string) - middle name
+ lastName: ghi (string) - last name
+ class: class (string) 
+ status: Present (string) - Absent,Present,
+ date: 2018-01-18 (string) - attendance date

## Attendancerequest (object)

+ regiNo: 123 (number, required) - Student registration number
+ date: date (string, required)- Date Formate date-month-year
+ class_id: 1 (number, required) - class id
+ section_id: 1 (number, required) - section id
+ session: 2018 (number, required) - Admission Year
+ status: Present (string, required)- Present,Absent

# Group Authentication

Authentication return token use this token to acess the apilacation user enter user name or password this api return token.

## Authentication [/authentication]

### Authentication parameter [POST]

+ Request (application/json)

        {
            "email": "Enter user email",
            "password":"Enter user password",
        }
+ Response 200 (application/json)

            {
                "token": "string"
            }
            
# Group Users 

## Users Collection [/users]

### List of all Users [GET]

+ Response 200 (application/json)

    + Attributes (array[User])

### Create a New User [POST]

You may create user using this action. It takes a JSON
object containing a user information.

+ Request (application/json)

    + Attributes (array[RequestUser])

+ Response 200 (application/json)

  
    + Body

            {
                "user_id": "Integer"
            }
            

## Single User [/users/{user_id}]
+ Parameters
    + user_id (number) - ID of the user in the form of an integer

### View a User Detail [GET]

+ Response 200 (application/json)

    + Attributes (array[User])

    
### Update User [PUT]

+ Request (application/json)

    + Attributes (array[RequestUser])

+ Response 200 (application/json)

    + Attributes (array[RequestUser])

            
### User Delete [DELETE]

+ Response 200



# Group Teachers

## Teachers Collection [/teachers]



### Get All Teachers [GET]

+ Response 200

    + Attributes (array[Teachers])

### Create New Teacher [POST]

+ Request (application/json) 

    + Attributes (array[Teachers])

+ Response 200 (application/json)

  
    + Body

            {
                "teacherid": "Integer"
            }
## Single Teacher [/teachers/{teacher_id}]
+ Parameters
    + teacher_id (number) - ID of the teacher in the form of an integer

### View a Teacher Detail [GET]

+ Response 200 (application/json)

    + Attributes (array[Teachers])

    
### Update Teacher [PUT]

+ Request (application/json)  
    
    + Attributes (array[Teachers])

+ Response 200 (application/json)

    + Attributes (array[Teachers])

    
##Teachers Sections [/teachers/{teacher_id}/sections]

List of all class and section assign to teacher

### View Teachers Assign Sections  [GET]

+ Response 200

    + Attributes (array[Teacherssection])

## Teachers Subjects [/teachers/{teacher_id}/subjects]

List of all subjects assign to teacher

### View Teacher Subjects [GET]

+ Response 200

    + Attributes (array[Teacherssubjects])

# Group Message

## Collaction of Messages [/messages]

Upload a wav file 

### Create New  Messages [POST]

+ Request (audio/wav)

     + Body 
     
     
            body(i.e file contents)
    
+ Response 200

    + Body

            {
                "File name": "filename.wav"
            }

### Get All Messages [GET]

+ Response 200
    
    + Attributes (array[Messages])



## Single Message [/messages/{file_name}]

### View a  Message [GET]

+ Response 200
    
    + Attributes (array[Messages])

### Update Message [PUT]

+ Request (audio/wav)

    + Attributes (array[Messages])

+ Response 200
   
   + Body

            {
                "File name": "filename.wav"
            }

### Delete a Message [DELETE]

+ Response 200

# Group Classes

## Classes Collection [/classes]



### Get All Classes [GET]

+ Response 200

    + Attributes (array[Classes])


### Create New Class [POST]

+ Request (application/json) 

    + Attributes (array[Classes])

+ Response 200 (application/json)

  
    + Body

            {
                "classid": "Integer"
            }

## Single Class [/classes/{class_id}]
+ Parameters
    + class_id (number) - ID of the class in the form of an integer

### View a Class Detail [GET]

+ Response 200 (application/json)

    + Attributes (array[Classes])

    
### Update Class [PUT]

+ Request (application/json)  
    
    + Attributes (array[Classes])

+ Response 200 (application/json)

    + Attributes (array[Classes])

    
## Class Sections [/classes/{class_id}/sections]

List of all class and section assign to teacher

### View Class Assign Sections  [GET]

+ Response 200

    + Attributes (array[Teacherssection])

## Class Notification [/classes/{class_id}/notification]

create call request class wise / dial

### Send Class wise Notification [POST]

first creat message then use this method

+ Request (application/json)

    + Attributes (array[Notifications])

+ Response 200

    + Body
    
    
            {
                "Success message"
            }

# Group Sections

## Sections Collection [/sections]

Also search by parameter(sections?class) e.g class=class_id

### Get All Sectionds [GET]

+ Response 200

    + Attributes (array[Sections])


### Create New Section [POST]

+ Request (application/json) 

    + Attributes (array[Sections])

+ Response 200 (application/json)

  
    + Body

            {
                "section": "Integer"
            }

## Single Section [/section/{section_id}]
+ Parameters
    + section_id (number) - ID of the section in the form of an integer

### View a Section Detail [GET]

+ Response 200 (application/json)

    + Attributes (array[Sections])

    
### Update Section [PUT]

+ Request (application/json)  
    
    + Attributes (array[Sections])

+ Response 200 (application/json)

    + Attributes (array[Sections])

    
## Section Students [/section/{section_id}/students]

List of all student according section id

### View Students Accoring to Section  [GET]

+ Response 200

    + Attributes (array[Students])

## Section Subjects [/section/{section_id}/subjects]

List of all student according section id

### View Students Accoring to Section  [GET]

+ Response 200

    + Attributes (array[Teacherssection])


## Section Notification [/section/{section_id}/notification]

create call request section wise / dial

### Send Section wise Notification [POST]

first creat message then use this method

+ Request (application/json)

    + Attributes (array[Notifications])

+ Response 200

    + Body
    
    
            {
                "Success message"
            }

# Group Students

## Students Collection [/students]

Also search by parameter(students?regiNo&class) or class,section,name,group e.g class=class_id,section=section_id

### Get All Students [GET]

+ Response 200

    + Attributes (array[Students])



## Single Student [/students/{studnet_id}]
+ Parameters
    + studnet_id (number) - ID of the student in the form of an integer

### View a Student Detail [GET]

+ Response 200 (application/json)

    + Attributes (array[Students])

    
### Update Student [PUT]

+ Request (application/json)  
    
    + Attributes (array[Students])

+ Response 200 (application/json)

    + Attributes (array[Students])

## Student Notification [/students/{student_id}/notification]

create call request section wise / dial

### Send Student Notification [POST]

first creat message then use this method

+ Request (application/json)

    + Attributes (array[Notifications])

+ Response 200

    + Body
    
    
            {
                "Success message"
            }

# Group Exam

##  Exam Collection [/exams]

### Get all Exams List [GET]

+ Response 200

    + Attributes (array[Exams])

## Single Exam [/exams/{exam_id}]
+ Parameters
    + exam_id (number) - ID of the exam in the form of an integer

### View a Exam Detail [GET]

+ Response 200 (application/json)

    + Attributes (array[Exams])

# Group Results 

## Results Collection [/results]

also search by parameter(results?regiNo&class) or class,section,name,exam,subject e.g class=class_id,section=section_id,exam=exam_id,subject=subject_code

### List of all results [GET]

+ Response 200 (application/json)

    + Attributes (array[Results])

### Create a New Result [POST]


+ Request (application/json)

    + Attributes (array[Resultsrequest])

+ Response 200 (application/json)

  
    + Body

            {
                "result_id": "Integer"
            }
            

## Single Result [/results/{result_id}]
+ Parameters
    + result_id (number) - ID of the result in the form of an integer

### View a Result Detail [GET]

+ Response 200 (application/json)

    + Attributes (array[Results])

    
### Update Result [PUT]

+ Request (application/json)

    + Attributes (array[Resultsrequest])

+ Response 200 (application/json)

    + Attributes (array[Resultsrequest])

            
### Result Delete [DELETE]

+ Response 200

# Group Attendance 

## Attendance Collection [/attendances]

Read / view complete attendances data and also search by parameter(attendaces?regiNo&date) or class,section,name date formate:`year-month-date`

### List of all Attendace [GET]

+ Response 200 (application/json)

    + Attributes (array[Attendances])

### Create a New Attendance [POST]


+ Request (application/json)

    + Attributes (array[Attendancerequest])

+ Response 200 (application/json)

  
    + Body

            {
                "attendance_id": "Integer"
            }
            

## Single Attendance [/attendances/{attendance_id}]
+ Parameters
    + attendance_id (number) - ID of the attendance in the form of an integer

### View a Attendance Detail [GET]

+ Response 200 (application/json)

    + Attributes (array[Attendances])

    
### Update Attendance [PUT]

+ Request (application/json)

    + Attributes (array[Attendancerequest])

+ Response 200 (application/json)

    + Attributes (array[Attendancerequest])

            
### Attendance Delete [DELETE]

+ Response 200

# Group Notifications 

## Notifications Collection [/notifications]


### List of all Notifications [GET]

+ Response 200 (application/json)

    + Attributes (array[Notifications])

### Create a New Notification [POST]


+ Request (application/json)

    + Attributes (array[Notifications])

+ Response 200 (application/json)

  
    + Body

            {
                "notification_id": "Integer"
            }
            

## Single Attendance [/notifications/{notification_id}]
+ Parameters
    + notification_id (number) - ID of the notification in the form of an integer

### View a Notification Detail [GET]

+ Response 200 (application/json)

    + Attributes (array[Notifications])

    
### Update Notification [PUT]

+ Request (application/json)

    + Attributes (array[Notifications])

+ Response 200 (application/json)

    + Attributes (array[Notifications])

            
### Notification Delete [DELETE]

+ Response 200







    






    



