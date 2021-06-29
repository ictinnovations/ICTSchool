Roles and Requirements for School System
========================================

School Basic Role
-----------------
1. Admin / School Admin
2. Teachers
3. Staff
4. Students

Admin / School Admin
--------------------
Admin will access management portal from web interface

### Dashboard
* Show total Students, Teacher
* Show Defaulters / Pending Payments
* Show Absent Students
* Show present Students
* etc...

### Subjects
* Create Subjects
* Delete Subjects
* Edit Subjects
* Export and Import Option

### Sections
* Create Sections

### Classes
* Create Classes
    * Select Subjects
    * Select Section
* Edit Class
* Delete Classes
* Import and Export Option 

### Teachers
* Add Teachers
* Edit Teachers
    * Import and Export Teacher List
* Assign Classes / Subjects
* Timetable Management
* Remove Teacher
* Show Assigned Classes / Subjects
	
### Students / Admission
* Add Students
    * Select Class
    * Enter Parents information like parent name and phone number
    * Enter Monthly Fees
    * Enter Discount in monthly fees
    * Monthly Fees (always create fee voucher according to monthly fees from fees management )
    * printable information
    * Send Voice / SMS  Admission Notification
* Edit Students
* Remove Students
* Import and Export Option
* Students Promotion
    * Promote into next class
    * Send Voice / SMS Promotion Notification
* Show Students List
    * Class / Section / Teacher Base
    * Admin can also see information of each students (marks, pending fees, all payments like that.)
* Send Voice / SMS notification (using IB APIs)

### Fees Management
* Show Defaulters List
    * Class and Section based (result like "Student_ID, Students_Name, Parent_Name And Pending Dues)
    * Show All Defaulters List
* Show paid fees list (students name ................)
* Create fee voucher (select payment from students profile)
* Create custom fee voucher
* able to change status into pending / paid / late with custom date
* Printable Voucher
* Send Voice Call reminder to defaulters after due date (using ICTBroadcast APIs)

### Exam Management
* Exam List
* Exam Grades
* Mange Marks
    * Select Exam, Class, Section and Subject
        * Show All students with marks of select option
        * admin can also edit records
* Create Exam Schedule 
* Send Voice / SMS reminder to Students for Exam Schedule (using ICTBroadcast APIs)	

### Teacher Timetable Management
	
### Search 
* Show Report of Individual Students
    * Absent Students
    * Fee Defaulters
    * Students Class Performance

Teachers
--------
Teacher control students record only of assigned classes

### Dashboard
* Show Class students
* Teacher Timetable
* Show absent and present students of assigned class
* Show Defaulters list of assigned class
	
### Attendance
* Show all students of assigned class
* Show these option (on-leave / absent / present) along with every students

### Students Dairy
* Students Daily Dairy Management

### Search

Staff
-----

### Dashboard
* Show Defaulters List
    * Class and Section based (result like "Student_ID, Students_Name, Parent_Name And Pending Dues)
    * Show All Defaulters List

### Fees Management
* Show paid fees list of students (students name ................)
* Create fee voucher (select payment from students profile)
* Create custom fee voucher
* able to change status into pending / paid / late with custom date
* Printable Voucher

Student / Parent
----------------
* Login Portal
* Check Students Performance 
* Class Result
* Fees (pending / paid)
* Attendance Reporting

