origin_driving_school/
├── assets/
│   ├── css/
│   │   └── style.css           # use your existing tokens, grids, components
│   ├── images/                 # your current images
│   └── js/
│       └── script.js           # navbar toggle, header on scroll, form hooks
├── includes/
│   ├── header.php              # <header data-header> … navbar … overlay
│   └── footer.php              # footer blocks + social links
├── config/
│   └── db.php                  # PDO connection (env-ready)
├── lib/
│   ├── auth.php                # login / session helpers
│   ├── validate.php            # server-side validation helpers
│   └── flash.php               # small flash message helper
├── models/
│   ├── Student.php
│   ├── Instructor.php
│   ├── Course.php
│   ├── Schedule.php
│   ├── Invoice.php
│   └── Vehicle.php
├── controllers/
│   ├── students.php            # list/create/update/delete
│   ├── instructors.php
│   ├── courses.php
│   ├── schedule.php
│   ├── invoices.php
│   └── vehicles.php

├── index.php               # Home (assessment banner + team)
├── about.php
├── courses.php
├── schedule.php
├── gallery.php
├── instructors.php
├── payments.php
├── contact.php
├── login.php
└── logout.php
└── sql/
    └── schema.sql