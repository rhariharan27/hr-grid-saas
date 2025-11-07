<?php

class Constants
{

  public const DateTimeFormat = 'd-m-Y h:i A';

  //Constants::DateTimeFormat
  public const DateFormat = 'd-m-Y';
  public const TimeFormat = 'h:i A';
  public const DateTimeHumanFormat = 'F j, Y, g:i a';
  public const DateTimeHumanFormatShort = 'M d, Y H:i';
  public const BaseFolderVisitImages = 'uploads/visitimages/';
  public const BaseFolderTaskUpdateFiles = 'uploads/taskupdatefiles/';
  public const BaseFolderLeaveRequestDocument = 'uploads/leaverequestdocuments/';
  public const BaseFolderExpenseProofs = 'uploads/expenseProofs/';
  public const BaseFolderUserDocumentRequest = 'uploads/userDocumentRequests/';
  public const BaseFolderEmployeeProfile = 'uploads/employeeProfilePictures';
  public const BaseFolderEmployeeProfileWithSlash = 'uploads/employeeProfilePictures/';
  public const BaseFolderEmployeeDocument = 'uploads/employeeDocuments/';
  public const BaseFolderChatFiles = 'uploads/chatFiles/';
  public const ALL_ADDONS_PURCHASE_LINK = 'https://czappstudio.com/laravel-addons/';
  public const All_ADDONS_ARRAY = [
    'GoogleRecaptcha' => [
      'name' => 'Google Recaptcha',
      'description' => 'This module is used to manage Google Recaptcha for login and registration.',
      'purchase_link' => 'https://czappstudio.com/product/google-recaptcha-addon-saas/',
    ],
    'Recruitment' => [
      'name' => 'Recruitment',
      'description' => 'This module is used to manage recruitment for employees.',
      'purchase_link' => 'https://czappstudio.com/product/recruitment-addon-saas/'
    ],
    'Calendar' => [
      'name' => 'Calendar',
      'description' => 'This module is used to manage calendar for employees.',
      'purchase_link' => 'https://czappstudio.com/product/calendar-addon-saas/',
    ],
    'BreakSystem' => [
      'name' => 'Break System',
      'description' => 'This module is used to manage breaks for employees.',
      'purchase_link' => 'https://czappstudio.com/product/break-system-addon-saas/',
    ],
    'DataImportExport' => [
      'name' => 'Data Import Export',
      'description' => 'This module is used to import and export data from the system.',
      'purchase_link' => 'https://czappstudio.com/product/data-import-export-addon-saas/',
    ],
    'DocumentManagement' => [
      'name' => 'Document Management',
      'description' => 'This module is used to manage document request for employees.',
      'purchase_link' => 'https://czappstudio.com/product/document-request-addon-saas/',
    ],
    'DynamicForms' => [
      'name' => 'Dynamic Forms',
      'description' => 'This module is used to create dynamic forms.',
      'purchase_link' => 'https://czappstudio.com/product/custom-forms-addon-saas/',
    ],
    'GeofenceSystem' => [
      'name' => 'Geofence System',
      'description' => 'This module is used to manage geofence for employees.',
      'purchase_link' => 'https://czappstudio.com/product/geofence-attendance-addon-saas/',
    ],
    'IpAddressAttendance' => [
      'name' => 'IP Address Attendance',
      'description' => 'This module is used to manage attendance based on IP Address.',
      'purchase_link' => 'https://czappstudio.com/product/ip-based-attendance-addon-saas/',
    ],
    'LoanManagement' => [
      'name' => 'Loan Management',
      'description' => 'This module is used to manage loans for employees.',
      'purchase_link' => 'https://czappstudio.com/product/loan-request-addon-saas/',
    ],
    /* 'ManagerApp' => [
       'name' => 'Manager App',
       'description' => 'This module is used to manage employees using a mobile app.',
       'purchase_link' => 'https://czappstudio.com/product/manager-app-field-manager-flutter/',
     ],*/
    'NoticeBoard' => [
      'name' => 'Notice Board',
      'description' => 'This module is used to manage notice board for employees.',
      'purchase_link' => 'https://czappstudio.com/product/notice-board-addon-saas/',
    ],
    'OfflineTracking' => [
      'name' => 'Offline Tracking',
      'description' => 'This module is used to track employees offline.',
      'purchase_link' => 'https://czappstudio.com/product/offline-tracking-addon-saas/',
    ],
    'PaymentCollection' => [
      'name' => 'Payment Collection',
      'description' => 'This module is used to collect payments from customers.',
      'purchase_link' => 'https://czappstudio.com/product/payment-collection-addon-saas/',
    ],
    'ProductOrder' => [
      'name' => 'Product Order',
      'description' => 'This module is used to manage product orders.',
      'purchase_link' => 'https://czappstudio.com/product/product-ordering-system-addon-saas/',
    ],
    'QRAttendance' => [
      'name' => 'QR Attendance',
      'description' => 'This module is used to manage attendance using QR Code.',
      'purchase_link' => 'https://czappstudio.com/product/qr-code-attendance-addon-saas/',
    ],
    'SiteAttendance' => [
      'name' => 'Site Attendance',
      'description' => 'This module is used to manage attendance based on site.',
      'purchase_link' => 'https://czappstudio.com/product/site-attendance-addon-saas/',
    ],
    'TaskSystem' => [
      'name' => 'Task System',
      'description' => 'This module is used to manage tasks for employees.',
      'purchase_link' => 'https://czappstudio.com/product/task-system-addon-saas/',
    ],
    'UidLogin' => [
      'name' => 'One Tap Login',
      'description' => 'This module is used to login using UID.',
      'purchase_link' => 'https://czappstudio.com/product/uid-login-addon-saas/',
    ],
    'AiChat' => [
      'name' => 'AI Business Assistant',
      'description' => 'This module is used to chat with AI.',
      'purchase_link' => 'https://czappstudio.com/net-saas-addons/',
    ],
    'DigitalIdCard' => [
      'name' => 'Digital ID Card',
      'description' => 'This module is used to manage digital ID cards for employees.',
      'purchase_link' => 'https://czappstudio.com/product/digital-id-card-saas/',
    ],
    'DynamicQrAttendance' => [
      'name' => 'Dynamic QR Attendance',
      'description' => 'This module is used to manage attendance using dynamic QR Code.',
      'purchase_link' => 'https://czappstudio.com/product/dynamic-qr-attendance-addon-saas/',
    ],
    'Payroll' => [
      'name' => 'Payroll Management',
      'description' => 'This module is used to manage payroll for employees.',
      'purchase_link' => 'https://czappstudio.com/product/payroll-management-addon-saas/',
    ],
    'SalesTarget' => [
      'name' => 'Sales Target',
      'description' => 'This module is used to manage sales targets for employees.',
      'purchase_link' => 'https://czappstudio.com/product/sales-target-addon-saas/',
    ],
    'StripeGateway' => [
      'name' => 'Stripe Payment Gateway',
      'description' => 'This module is used to manage payments using Stripe.',
      'purchase_link' => 'https://czappstudio.com/product/stripe-payment-gateway/',
    ],
    'FaceAttendance' => [
      'name' => 'Face Attendance',
      'description' => 'This module is used to manage attendance using face recognition.',
      'purchase_link' => 'https://czappstudio.com/product/face-attendance-addon-saas/',
    ],
    'Approvals' => [
      'name' => 'Approvals',
      'description' => 'This module is used to manage approvals for employees from mobile app.',
      'purchase_link' => 'https://czappstudio.com/product/approvals-addon-saas/',
    ],
  ];
  public const BuiltInRoles = ['admin', 'hr', 'field_employee', 'office_employee', 'manager'];

}
