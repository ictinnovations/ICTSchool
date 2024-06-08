Authentication
--------------
* POST authenticate
* ~~POST authenticate/cancel~~

Admin / Staff / Students
------------------------
* ~~GET users~~
* POST users
* GET users/{user_id}
* PUT users/{user_id}
* ~~DELETE users/{user_id}~~

Teachers
--------
* GET teachers
* ~~POST teachers~~
* GET teachers/{teacher_id}
* PUT teachers/{teacher_id}
* ~~DELETE teachers/{teacher_id}~~
* ~~GET teachers/{teacher_id}/classes~~
* GET teachers/{teacher_id}/sections
* ~~GET teachers/{teacher_id}/students~~
* GET teachers/{teacher_id}/subjects
* __GET teachers/{teacher_id}/attendances__



Classes
-------
* GET classes
* GET classes/{class_id}
* PUT classes/{class_id}
* GET classes/{class_id}/sections
* ~~GET classes/{class_id}/subjects~~
* ~~GET classes/{class_id}/teachers~~
* ~~GET classes/{class_id}/exams~~
* POST classes/{class_id}/notifications
* __GET classes/{class_id}/attendances__
* __GET classes/{class_id}/attendances/history__

Sections
--------
* GET sections
* GET sections/{section_id}
* ~~PUT sections/{section_id}~~
* GET sections/{section_id}/subjects
* GET sections/{section_id}/students
* GET sections/{section_id}/teachers ; i.e associated teachers
* ~~GET sections/{section_id}/exams~~
* POST sections/{section_id}/notifications
* __GET sections/{section_id}/attendances__
* __GET sections/{section_id}/attendances/history__

Students
--------
* GET students
* GET students/{student_id}
* PUT students/{student_id}
* ~~GET students/{student_id}/subjects~~
* ~~GET students/{student_id}/exams~~
* ~~GET students/{student_id}/teachers~~
* __GET students/{student_id}/attendances__
* POST students/{student_id}/notifications

Subjects
--------
* ~~GET subjects~~
* ~~POST subjects~~
* ~~GET subjects/{subject_id}~~
* ~~PUT subjects/{subject_id}~~
* ~~DELETE subjects/{subject_id}~~

Exams
-----
* GET exams
* ~~POST exams~~
* GET exams/{exam_id}
* ~~PUT exams/{exam_id}~~
* ~~DELETE exams/{exam_id}~~

Results
-------
* GET results
* POST results
* GET results/{result_id}
* PUT results/{result_id}
* DELETE results/{result_id}

Notifications
-------------
* GET notifications
* POST notifications
* GET notifications/{notification_id}
* PUT notifications/{notification_id}
* DELETE notifications/{notification_id}

Attendence
----------
* GET attendances
* POST attendances
* GET attendances/{attendance_id}
* PUT attendances/{attendance_id}
* DELETE attendances/{attendance_id}

