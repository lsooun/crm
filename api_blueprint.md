FORMAT: 1A

# API_CRM

# Auth
Auth routes

## Login to system [POST /login]


+ Request (application/json)
    + Body

            {
                "email": "admin@mw.life",
                "password": "bar"
            }

+ Response 200 (application/json)
    + Body

            {
                "token": "token",
                "user": {
                    "id": 4,
                    "first_name": "Admin",
                    "last_name": "Doe",
                    "email": "admin@mw.life",
                    "phone_number": "465465415",
                    "user_id": "1",
                    "user_avatar": "image.jpg",
                    "permissions": "{sales_team.read:true,sales_team.write:true,sales_team.delete:true,leads.read:true,leads.write:true,leads.delete:true,opportunities.read:true,opportunities.write:true,opportunities.delete:true,logged_calls.read:true,logged_calls.write:true,logged_calls.delete:true,meetings.read:true,meetings.write:true,meetings.delete:true,products.read:true,products.write:true,products.delete:true,quotations.read:true,quotations.write:true,quotations.delete:true,sales_orders.read:true,sales_orders.write:true,sales_orders.delete:true,invoices.read:true,invoices.write:true,invoices.delete:true,pricelists.read:true,pricelists.write:true,pricelists.delete:true,contracts.read:true,contracts.write:true,contracts.delete:true,staff.read:true,staff.write:true,staff.delete:true}"
                },
                "role": "user",
                "date_format": "2017-10-10",
                "time_format": "10:15",
                "date_time_format": "2017-10-10 10:15"
            }

+ Response 401 (application/json)
    + Body

            {
                "error": "invalid_credentials"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "could_not_create_token"
            }

## Edit profile [POST /edit_profile]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "first_name": "First",
                "last_name": "Last",
                "phone_number": "+356421544",
                "email": "email@email.com",
                "password": "password",
                "password_confirmation": "password",
                "avatar": "base64_encoded_image"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Create profile from staff invite [POST /create_profile_invite]


+ Request (application/json)
    + Body

            {
                "first_name": "First",
                "last_name": "Last",
                "phone_number": "+356421544",
                "password": "password",
                "code": "invite_code"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Create profile from staff invite [POST /update_password]


+ Request (application/json)
    + Body

            {
                "code": "foo",
                "id": 1,
                "password": "password"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all email [GET /emails]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "emails": [
                    {
                        "get_emails": [
                            {
                                "id": 14,
                                "assign_customer_id": 0,
                                "to": "1",
                                "from": "1",
                                "subject": "dfgdfg",
                                "message": "dfgfdg",
                                "read": 0,
                                "delete_sender": 0,
                                "delete_receiver": 0,
                                "created_at": "2017-06-23 11:05:46",
                                "updated_at": "2017-06-23 11:05:46",
                                "deleted_at": null,
                                "sender": {
                                    "id": 1,
                                    "email": "admin@mw.life",
                                    "last_login": "2017-06-23 14:02:43",
                                    "first_name": "Admin",
                                    "last_name": "Admin",
                                    "phone_number": null,
                                    "user_avatar": null,
                                    "user_id": 1,
                                    "created_at": "2017-03-02 16:09:12",
                                    "updated_at": "2017-06-23 14:02:43",
                                    "deleted_at": null,
                                    "full_name": "Admin Admin",
                                    "avatar": "http://localhost:81/crm54/public/uploads/avatar/user.png"
                                }
                            }
                        ],
                        "sent_emails": [
                            {
                                "id": 14,
                                "assign_customer_id": 0,
                                "to": "1",
                                "from": "1",
                                "subject": "dfgdfg",
                                "message": "dfgfdg",
                                "read": 0,
                                "delete_sender": 0,
                                "delete_receiver": 0,
                                "created_at": "2017-06-23 11:05:46",
                                "updated_at": "2017-06-23 11:05:46",
                                "deleted_at": null,
                                "receiver": {
                                    "id": 1,
                                    "email": "admin@mw.life",
                                    "last_login": "2017-06-23 14:02:43",
                                    "first_name": "Admin",
                                    "last_name": "Admin",
                                    "phone_number": null,
                                    "user_avatar": null,
                                    "user_id": 1,
                                    "created_at": "2017-03-02 16:09:12",
                                    "updated_at": "2017-06-23 14:02:43",
                                    "deleted_at": null,
                                    "full_name": "Admin Admin",
                                    "avatar": "http://localhost:81/crm54/public/uploads/avatar/user.png"
                                }
                            }
                        ]
                    }
                ]
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get single email [GET /email]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "email_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "email": {
                    "id": 1,
                    "assign_customer_id": 0,
                    "to": "1",
                    "from": "1",
                    "subject": "dfgdfg",
                    "message": "dfgfdg",
                    "read": 1,
                    "delete_sender": 0,
                    "delete_receiver": 0,
                    "created_at": "2017-06-23 11:05:46",
                    "updated_at": "2017-06-23 14:34:56",
                    "deleted_at": null,
                    "sender": {
                        "id": 1,
                        "email": "admin@mw.life",
                        "last_login": "2017-06-23 14:02:43",
                        "first_name": "Admin",
                        "last_name": "Admin",
                        "phone_number": null,
                        "user_avatar": null,
                        "user_id": 1,
                        "created_at": "2017-03-02 16:09:12",
                        "updated_at": "2017-06-23 14:02:43",
                        "deleted_at": null,
                        "full_name": "Admin Admin",
                        "avatar": "http://localhost:81/crm54/public/uploads/avatar/user.png"
                    }
                }
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post email [POST /post_email]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "message": "This is message",
                "recipients": [
                    1,
                    2,
                    3
                ],
                "subject": "Email subject"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete email [POST /delete_email]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "email_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Replay email [POST /replay_email]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "message": "This is message",
                "email_id": 1
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Password recovery [POST /password_recovery]


+ Request (application/json)
    + Body

            {
                "email": "admin@sms.com"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 201 (application/json)
    + Body

            {
                "error": "user_dont_exists"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

# User [/user]
User and staff endpoints, can be accessed only with role "user" or "staff"

## Get all calendar items [GET /user/calendar]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "salesteam": [
                    {
                        "id": 1,
                        "title": "Name of team",
                        "start_date": "2016-12-12",
                        "end_date": "2016-12-12",
                        "all_day": true,
                        "type": "quotation"
                    }
                ]
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get settings [GET /user/settings]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "settings": {
                    "site_name": "zxiaoke",
                    "site_logo": "591d9416b9e91.jpg",
                    "site_email": "admin@mw.life",
                    "allowed_extensions": "gif,jpg,jpeg,png,pdf,txt",
                    "backup_type": "local",
                    "email_driver": "mail",
                    "minimum_characters": "",
                    "date_format": "m/d/Y",
                    "time_format": "g:i A",
                    "currency": "USD",
                    "email_host": "",
                    "email_port": "",
                    "email_username": "",
                    "email_password": "",
                    "address1": "",
                    "address2": "",
                    "phone": "",
                    "fax": "",
                    "currency_position": "right",
                    "max_upload_file_size": "1000",
                    "sales_tax": "0",
                    "payment_term1": "1",
                    "payment_term2": "2",
                    "payment_term3": "3",
                    "opportunities_reminder_days": "10",
                    "contract_renewal_days": "10",
                    "invoice_reminder_days": "123",
                    "quotation_prefix": "q_",
                    "quotation_start_number": "1",
                    "quotation_template": "quotation_red_green",
                    "sales_prefix": "s_",
                    "sales_start_number": "2",
                    "saleorder_template": "saleorder_red_green",
                    "invoice_prefix": "i",
                    "invoice_start_number": "3",
                    "invoice_template": "invoice_red_green",
                    "invoice_payment_prefix": "",
                    "invoice_payment_start_number": "",
                    "pusher_app_id": "",
                    "pusher_key": "",
                    "pusher_secret": "",
                    "paypal_username": "",
                    "paypal_password": "",
                    "paypal_signature": "",
                    "stripe_secret": "",
                    "stripe_publishable": "",
                    "pdf_logo": "logo_1492175789.png",
                    "jquery_date": "MM/DD/GGGG",
                    "jquery_date_time": "MM/DD/GGGG h:mm A"
                },
                "logo": "http://zxiaoke.cn/image.jpg",
                "pdf_logo": "http://zxiaoke.cn/image.jpg",
                "max_upload_file_size": {
                    "1000": "1MB",
                    "2000": "2MB",
                    "3000": "3MB",
                    "4000": "4MB",
                    "5000": "5MB",
                    "6000": "6MB",
                    "7000": "7MB",
                    "8000": "8MB",
                    "9000": "9MB",
                    "10000": "10MB"
                },
                "currency": {
                    "USD": "USD",
                    "EUR": "EUR"
                },
                "backup_type": [
                    {
                        "text": "Local",
                        "id": "local"
                    },
                    {
                        "text": "Dropbox",
                        "id": "dropbox"
                    },
                    {
                        "text": "Amazon S3",
                        "id": "s3"
                    }
                ]
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post call [POST /user/update_settings]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "logo": "base64:image",
                "pdf_logo": "base64:image",
                "date_format_custom": "d-m-Y",
                "time_format_custom": "H:m",
                "site_name": "CRM",
                "address1": "Address1",
                "address2": "Address2",
                "site_email": "email@email.com",
                "currency": "USD"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all calls [GET /user/calls]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "calls": [
                    {
                        "id": 1,
                        "date": "2015-10-15",
                        "call_summary": "Call summary",
                        "company": "Company",
                        "user": "User"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get call item [GET /user/call]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "call_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "call": {
                    "id": 1,
                    "date": "2015-10-15",
                    "call_summary": "Call summary",
                    "duration": "30",
                    "company": "Company",
                    "resp_staff": "User",
                    "user_id": 1,
                    "created_at": "2015-12-22 20:17:20",
                    "updated_at": "2015-12-22 20:19:11",
                    "deleted_at": null
                }
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post call [POST /user/post_call]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "date": "2015-10-11",
                "call_summary": "call summary",
                "duration": "30",
                "company_id": "1",
                "resp_staff_id": "12"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit call [POST /user/edit_call]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "call_id": "1",
                "date": "2015-10-11",
                "call_summary": "call summary",
                "company_id": "1",
                "resp_staff_id": "12"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete call [POST /user/delete_call]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "call_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all categories [GET /user/categories]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "category": [
                    {
                        "id": 1,
                        "name": "Category name"
                    }
                ]
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get category item [GET /user/category]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "category_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "category": {
                    "id": 1,
                    "name": "Category",
                    "user_id": 1,
                    "created_at": "2015-12-23 16:58:25",
                    "updated_at": "2015-12-23 16:58:25",
                    "deleted_at": null
                }
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post category [POST /user/post_category]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "name": "category name"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit category [POST /user/edit_category]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "category_id": "1",
                "name": "category name"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete category [POST /user/delete_category]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "call_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all companies [GET /user/companies]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "companies": [
                    {
                        "id": 1,
                        "name": "Name",
                        "customer": "customer name",
                        "phone": "634654165456"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get company item [POST /user/company]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "company_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "company": {
                    "id": 2,
                    "name": "dg dfg",
                    "email": "user@mw.life",
                    "password": "",
                    "lostpw": "",
                    "address": "fdgdfg",
                    "website": "gfdgfdg",
                    "phone": "45454",
                    "mobile": "45",
                    "fax": "4545",
                    "title": "",
                    "company_avatar": "",
                    "company_attachment": "",
                    "main_contact_person": 3,
                    "sales_team_id": 1,
                    "country_id": 1,
                    "state_id": 43,
                    "city_id": 5914,
                    "longitude": "63.30929400000002",
                    "latitude": "35.6403478",
                    "user_id": 1,
                    "created_at": "2015-12-26 07:10:25",
                    "updated_at": "2015-12-26 07:10:25",
                    "deleted_at": null
                }
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post company [POST /user/post_company]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "name": "Company name",
                "email": "email@email.com",
                "address": "first street,NY",
                "sales_team_id": "1",
                "main_contact_person": "1",
                "phone": "123132214",
                "avatar": "base64_encoded_image"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit company [POST /user/edit_company]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "company_id": "1",
                "name": "Company name",
                "email": "email@email.com",
                "avatar": "base64_encoded_image"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete company [POST /user/delete_company]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "company_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all contracts [GET /user/contracts]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "company": [
                    {
                        "id": 1,
                        "start_date": "2015-11-12",
                        "description": "Description",
                        "name": "Company name",
                        "user": "User name"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get contract item [GET /user/contract]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "contract_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "contract": {
                    "id": 1,
                    "start_date": "21.12.2015.",
                    "end_date": "23.12.2015.",
                    "description": "ffdgfdg",
                    "company_id": 1,
                    "resp_staff_id": 2,
                    "real_signed_contract": "",
                    "user_id": 1,
                    "created_at": "2015-12-22 20:27:37",
                    "updated_at": "2015-12-22 20:27:37",
                    "deleted_at": null
                }
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post contract [POST /user/post_contract]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "start_date": "2015-11-11",
                "end_date": "2015-11-11",
                "description": "Description",
                "company_id": "1",
                "resp_staff_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit contract [POST /user/edit_contract]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "company_id": "1",
                "name": "Company name",
                "email": "email@email.com"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete contract [POST /user/delete_contract]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "company_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all customers [GET /user/customers]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "customers": [
                    {
                        "user_id": 1,
                        "customer_id": 2,
                        "full_name": "full name",
                        "email": "email@email.com",
                        "created_at": "2015--11-11",
                        "avatar": "http://avatar.com/avatar.jpg"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get customer item [GET /user/customer]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "customer_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "contract": {
                    "id": 1,
                    "user_id": 3,
                    "belong_user_id": 2,
                    "address": "",
                    "website": "",
                    "job_position": "",
                    "mobile": "5456",
                    "fax": "",
                    "title": "",
                    "company_id": 0,
                    "sales_team_id": 0,
                    "created_at": "2015-12-22 19:26:19",
                    "avatar": "http://avatar.com/avatar.jpg"
                }
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post customer [POST /user/post_customer]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "first_name": "first name",
                "last_name": "last name",
                "email": "email@email.com",
                "password": "password",
                "password_confirmation": "password",
                "phone_number": "+54212425",
                "sales_team_id": 1,
                "company_id": 1,
                "address": "address",
                "job_position": "developer",
                "mobile": " +545231",
                "fax": "+2314521",
                "title": "mr"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit customer [POST /user/edit_customer]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "customer_id": "1",
                "first_name": "first name",
                "last_name": "last name",
                "email": "email@email.com",
                "sales_team_id": 1,
                "company_id": 1,
                "address": "address",
                "phone_number": "+54212425",
                "0": "address",
                "job_position": "developer",
                "mobile": " +545231",
                "fax": "+2314521",
                "title": "mr"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete customer [POST /user/delete_customer]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "customer_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all invoices [GET /user/invoices]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "invoices": [
                    {
                        "id": 1,
                        "invoice_number": "1465456",
                        "invoice_date": "2015-11-11",
                        "customer": "Customer Name",
                        "unpaid_amount": "15.2",
                        "status": "Status",
                        "due_date": "2015-11-11"
                    }
                ],
                "month_overdue": 1,
                "month_paid": 5,
                "month_open": 3
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get invoice item [GET /user/invoice]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "invoice_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "invoice": {
                    "id": 1,
                    "order_id": 0,
                    "customer_id": 3,
                    "sales_person_id": "2",
                    "sales_team_id": 1,
                    "invoice_number": "I0001",
                    "invoice_date": "08.12.2015. 00:00",
                    "due_date": "24.12.2015. 00:00",
                    "payment_term": "10",
                    "status": "Open Invoice",
                    "total": 1221,
                    "tax_amount": 195.36,
                    "grand_total": 1416.36,
                    "discount": 10,
                    "final_price": 1216.36,
                    "unpaid_amount": 1173.06,
                    "user_id": 1,
                    "created_at": "2015-12-23 18:05:35",
                    "updated_at": "2015-12-28 19:21:48",
                    "deleted_at": null
                },
                "products": {
                    "product": "product",
                    "description": "description",
                    "quantity": 3,
                    "unit_price": 1.95,
                    "taxes": 1.55,
                    "subtotal": 195.36
                }
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post invoice [POST /user/post_invoice]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "customer_id": "5",
                "invoice_date": "2015-11-11",
                "sales_person_id": "2",
                "status": "status",
                "total": "10.00",
                "tax_amount": "01.10",
                "grand_total": "11.10",
                "discount": 1.2,
                "final_price": 9.85,
                "invoice_prefix": "I00",
                "invoice_start_number": "0"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit invoice [POST /user/edit_invoice]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "invoice_id": "1",
                "customer_id": "5",
                "invoice_date": "2015-11-11",
                "sales_person_id": "2",
                "status": "status",
                "total": "10.00",
                "tax_total": "01.10",
                "grand_total": "11.10",
                "discount": "0.10",
                "final_price": "9.10"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete invoice [POST /user/delete_invoice]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "invoice_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all invoice_payments [GET /user/invoice_payments]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "invoice_payments": [
                    {
                        "id": 1,
                        "payment_number": "P002",
                        "payment_received": "1525.26",
                        "payment_method": "Paypal",
                        "payment_date": "2015-11-11",
                        "customer": "Customer Name",
                        "person": "Person Name"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get invoice_payment item [GET /user/invoice_payment]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "invoice_payment": [
                    {
                        "id": 1,
                        "payment_number": "P002",
                        "payment_received": "1525.26",
                        "payment_method": "Paypal",
                        "payment_date": "2015-11-11",
                        "customer": "Customer Name",
                        "person": "Person Name"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post invoice_payment [POST /user/post_invoice_payment]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "invoice_id": "5",
                "payment_date": "2015-11-11",
                "payment_method": "2",
                "payment_received": "555"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all leads [GET /user/leads]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "leads": [
                    {
                        "id": 1,
                        "register_time": "2015-12-22",
                        "opportunity": "1.2",
                        "contact_name": "Contact name",
                        "email": "dsad@asd.com",
                        "phone": "456469465",
                        "salesteam": "Test team"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get lead item [GET /user/lead]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "lead_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "invoice": {
                    "id": 1,
                    "opportunity": "Lead",
                    "company_name": "sdfsdf sdf",
                    "customer_id": 1,
                    "address": "sd fsdfd",
                    "country_id": 1,
                    "state_id": 43,
                    "city_id": 5914,
                    "sales_person_id": 1,
                    "sales_team_id": 1,
                    "contact_name": "sdfsdf sdf sdf ",
                    "title": "Doctor",
                    "email": "user@mw.life",
                    "function": "asdasd sad asd ",
                    "phone": "1545",
                    "mobile": "545",
                    "fax": "1545",
                    "tags": "2,4",
                    "priority": "Low",
                    "internal_notes": "asd asd asd ",
                    "assigned_partner_id": 0,
                    "user_id": 1,
                    "created_at": "2015-12-22 19:56:54",
                    "updated_at": "2015-12-22 19:56:54",
                    "deleted_at": null
                }
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post lead [POST /user/post_lead]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "opportunity": "125.5",
                "email": "test@test.com",
                "customer_id": "12",
                "sales_team_id": "1",
                "tags": "Softwae",
                "country_id": "15",
                "sales_person_id": "12"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit lead [POST /user/edit_lead]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "lead_id": 1,
                "opportunity": "125.5",
                "email": "test@test.com",
                "customer_id": "12",
                "sales_team_id": "1",
                "tags": "Softwae",
                "country_id": "15"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete lead [POST /user/delete_lead]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "lead_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all lead calls [GET /user/lead_calls]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "lead_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "calls": [
                    {
                        "id": 1,
                        "date": "2015-10-15",
                        "call_summary": "Call summary",
                        "company": "Company",
                        "responsible": "User"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all lead call [GET /user/lead_call]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "lead_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "calls": [
                    {
                        "id": 1,
                        "date": "2015-10-15",
                        "call_summary": "Call summary",
                        "company": "Company",
                        "responsible": "User"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post lead call [POST /user/post_lead_call]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "lead_id": "1",
                "date": "2015-10-11",
                "call_summary": "call summary",
                "company_id": "1",
                "resp_staff_id": "12"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit lead call [POST /user/edit_lead_call]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "call_id": "1",
                "lead_id": "1",
                "date": "2015-10-11",
                "call_summary": "call summary",
                "company_id": "1",
                "resp_staff_id": "12"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete lead call [POST /user/delete_lead_call]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "call_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all meetings [GET /user/meetings]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "meetings": [
                    {
                        "id": 1,
                        "meeting_subject": "meeting subject",
                        "starting_date": "2015-12-22",
                        "ending_date": "2015-12-22",
                        "responsible": "User name"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get meeting item [GET /user/meeting]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "meeting_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "meeting": {
                    "id": 2,
                    "meeting_subject": "Meeting",
                    "attendees": "1",
                    "responsible_id": 2,
                    "starting_date": "29.12.2015. 00:00",
                    "ending_date": "08.01.2016. 00:00",
                    "all_day": 0,
                    "location": "sdfsdf",
                    "meeting_description": "ftyf hgfhgfh",
                    "privacy": "Everyone",
                    "show_time_as": "Free",
                    "duration": "",
                    "user_id": 0,
                    "created_at": "2015-12-22 20:19:42",
                    "updated_at": "2015-12-26 15:03:37",
                    "deleted_at": null
                }
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post meeting [POST /user/post_meeting]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "meeting_subject": "Subject",
                "starting_date": "2015-11-11",
                "ending_date": "2015-11-11"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit meeting [POST /user/edit_meeting]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "meeting_id": 1,
                "meeting_subject": "Subject",
                "starting_date": "2015-11-11",
                "ending_date": "2015-11-11"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete meeting [POST /user/delete_meeting]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "meeting_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all opportunity calls [GET /user/opportunity_calls]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "opportunity_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "calls": [
                    {
                        "id": 1,
                        "date": "2015-10-15",
                        "call_summary": "Call summary",
                        "company": "Company",
                        "responsible": "User"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get opportunity call [GET /user/opportunity_call]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "opportunity_id": "1",
                "call_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "calls": {
                    "id": 1,
                    "date": "2015-10-15",
                    "call_summary": "Call summary",
                    "company": "Company",
                    "responsible": "User"
                }
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post opportunity call [POST /user/post_opportunity_call]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "opportunity_id": "1",
                "date": "2015-10-11",
                "call_summary": "call summary",
                "company_id": "1",
                "resp_staff_id": "12"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit opportunity call [POST /user/edit_opportunity_call]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "opportunity_id": "1",
                "lead_id": "1",
                "date": "2015-10-11",
                "call_summary": "call summary",
                "company_id": "1",
                "resp_staff_id": "12"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete opportunity call [POST /user/delete_opportunity_call]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "call_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all opportunities [GET /user/opportunities]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "opportunities": [
                    {
                        "id": 1,
                        "opportunity": "Opportunity",
                        "company": "Company",
                        "next_action": "2015-12-22",
                        "stages": "Stages",
                        "expected_revenue": "Expected revenue",
                        "probability": "probability",
                        "salesteam": "salesteam",
                        "calls": "5",
                        "meetings": "5"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get opportunity item [GET /user/opportunity]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "opportunity_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "opportunity": {
                    "id": 1,
                    "opportunity": "r dfgfdg dfg",
                    "stages": "New",
                    "customer_id": 1,
                    "expected_revenue": "sad asd ",
                    "probability": "0",
                    "email": "admin@gmail.com",
                    "phone": 787889,
                    "sales_person_id": 2,
                    "sales_team_id": 1,
                    "next_action": "21.12.2015.",
                    "next_action_title": "454545",
                    "expected_closing": "29.12.2015.",
                    "priority": "Low",
                    "tags": "1,3",
                    "lost_reason": "Too expensive",
                    "internal_notes": "ghkhjkhjk",
                    "assigned_partner_id": 1,
                    "user_id": 1,
                    "created_at": "2015-12-22 20:17:20",
                    "updated_at": "2015-12-22 20:19:11",
                    "deleted_at": null
                }
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post opportunity [POST /user/post_opportunity]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "opportunity": "Opportunity",
                "stages": "New",
                "email": "email@email.com",
                "customer": "1",
                "sales_team_id": "1",
                "next_action": "2015-11-11",
                "expected_closing": "2015-11-11"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit opportunity [POST /user/edit_opportunity]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "opportunity_id": 1,
                "stages": "New",
                "opportunity": "Opportunity",
                "email": "email@email.com",
                "customer": "1",
                "sales_team_id": "1",
                "next_action": "2015-11-11",
                "expected_closing": "2015-11-11"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete opportunity [POST /user/delete_opportunity]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "opportunity_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all opportunity meetings [GET /user/opportunity_meetings]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "opportunity_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "salesteam": [
                    {
                        "id": 1,
                        "meeting_subject": "meeting subject",
                        "starting_date": "2015-12-22",
                        "ending_date": "2015-12-22",
                        "responsible": "User name"
                    },
                    {
                        "id": 1,
                        "meeting_subject": "meeting subject",
                        "starting_date": "2015-12-22",
                        "ending_date": "2015-12-22",
                        "responsible": "User name"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get opportunity meeting [GET /user/opportunity_meeting]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "opportunity_id": "1",
                "meeting_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "meetings": {
                    "id": 1,
                    "meeting_subject": "meeting subject",
                    "starting_date": "2015-12-22",
                    "ending_date": "2015-12-22",
                    "responsible": "User name"
                }
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post opportunity meeting [POST /user/post_opportunity_meeting]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "opportunity_id": 1,
                "meeting_subject": "Subject",
                "starting_date": "2015-11-11 10:15AM",
                "ending_date": "2015-11-11 10:30AM",
                "responsible_id": 1
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit opportunity meeting [POST /user/edit_opportunity_meeting]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "meeting_id": 1,
                "opportunity_id": 1,
                "meeting_subject": "Subject",
                "starting_date": "2015-11-11 10:15AM",
                "ending_date": "2015-11-11 10:30AM",
                "responsible_id": 1
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete opportunity meeting [POST /user/delete_opportunity_meeting]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "meeting_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all products [GET /user/products]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "products": [
                    {
                        "id": 1,
                        "product_name": "product name",
                        "name": "category",
                        "product_type": "Type",
                        "status": "1",
                        "quantity_on_hand": "12",
                        "quantity_available": "52"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get product item [GET /user/product]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "product_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "product": {
                    "id": 1,
                    "product_name": "product",
                    "product_image": "",
                    "category_id": 1,
                    "product_type": "Consumable",
                    "status": "In Development",
                    "quantity_on_hand": 12,
                    "quantity_available": 22,
                    "sale_price": 1,
                    "description": "sdfdsfsdf",
                    "description_for_quotations": "sdfsdfsdfsdf",
                    "user_id": 1,
                    "created_at": "2015-12-23 16:58:51",
                    "updated_at": "2015-12-26 07:24:51",
                    "deleted_at": null
                }
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post product [POST /user/post_product]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "product_name": "product name",
                "sale_price": "15.2",
                "description": "sadsadsd",
                "quantity_on_hand": "12",
                "quantity_available": "11"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit product [POST /user/edit_product]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "product_id": "1",
                "product_name": "product name",
                "sale_price": "15.2",
                "description": "sadsadsd",
                "quantity_on_hand": "12",
                "quantity_available": "11"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete product [POST /user/delete_product]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "product_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all qtemplates [GET /user/qtemplates]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "qtemplates": [
                    {
                        "id": 1,
                        "quotation_template": "product name",
                        "quotation_duration": "10"
                    }
                ]
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get qtemplate item [GET /user/qtemplate]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "qtemplate_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "qtemplate": {
                    "id": 1,
                    "quotation_template": "testaa",
                    "quotation_duration": 19,
                    "immediate_payment": 0,
                    "terms_and_conditions": "sd f sdf 22",
                    "total": 2553,
                    "tax_amount": 408.48,
                    "grand_total": 2961.48,
                    "user_id": 1,
                    "created_at": "2015-12-23 18:45:58",
                    "updated_at": "2015-12-23 18:46:21",
                    "deleted_at": null
                },
                "products": {
                    "product": "product",
                    "description": "description",
                    "quantity": 3,
                    "unit_price": 1.95,
                    "taxes": 1.55,
                    "subtotal": 195.36
                }
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post qtemplate [POST /user/post_qtemplate]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "product_name": "product name",
                "sale_price": "15.2",
                "description": "sadsadsd",
                "quantity_on_hand": "12",
                "quantity_available": "11",
                "total": "10.00",
                "tax_amount": "1.11",
                "grand_total": "11.11"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit qtemplate [POST /user/edit_qtemplate]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "qtemplate_id": "1",
                "product_name": "product name",
                "sale_price": "15.2",
                "description": "sadsadsd",
                "quantity_on_hand": "12",
                "quantity_available": "11",
                "total": "10.00",
                "tax_amount": "1.11",
                "grand_total": "11.11"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete qtemplate [POST /user/delete_qtemplate]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "qtemplate_id": "1"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all quotations [GET /user/quotations]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "quotations": [
                    {
                        "id": 1,
                        "quotations_number": "4545",
                        "date": "2015-11-11",
                        "customer": "customer name",
                        "person": "person name",
                        "final_price": "12",
                        "status": "1"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get quotation item [GET /user/quotation]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "quotation_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "quotation": {
                    "id": 1,
                    "quotations_number": "Q0001",
                    "customer_id": 3,
                    "qtemplate_id": 0,
                    "date": "08.12.2015. 00:00",
                    "exp_date": "30.12.2015.",
                    "payment_term": "10",
                    "sales_person_id": 2,
                    "sales_team_id": 1,
                    "terms_and_conditions": "dff dfg dfg",
                    "status": "Draft Quotation",
                    "total": 333,
                    "tax_amount": 53.28,
                    "grand_total": 386.28,
                    "discount": 11.28,
                    "final_price": 289.28,
                    "user_id": 1,
                    "created_at": "2015-12-23 18:39:12",
                    "updated_at": "2015-12-23 18:39:12",
                    "deleted_at": null
                },
                "products": {
                    "product": "product",
                    "description": "description",
                    "quantity": 3,
                    "unit_price": 1.95,
                    "taxes": 1.55,
                    "subtotal": 195.36
                }
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post quotation [POST /user/post_quotation]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "customer_id": "1",
                "date": "2015-11-11",
                "qtemplate_id": "1",
                "payment_term": "term",
                "sales_person_id": "1",
                "sales_team_id": "1",
                "grand_total": "12.5",
                "discount": "10.2",
                "final_price": "10.25",
                "quotation_prefix": "Q00",
                "quotation_start_number": "0"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit quotation [POST /user/edit_quotation]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "quotation_id": "1",
                "customer_id": "1",
                "date": "2015-11-11",
                "qtemplate_id": "1",
                "payment_term": "term",
                "sales_person": "1",
                "sales_team_id": "1",
                "grand_total": "12.5",
                "discount": "10.2",
                "final_price": "10.25"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete quotation [POST /user/delete_quotation]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "quotation_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all sales orders [GET /user/sales_orders]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "salesorders": [
                    {
                        "id": 1,
                        "quotations_number": "product name",
                        "date": "2015-11-11",
                        "customer": "customer name",
                        "person": "sales person name",
                        "final_price": "12.53",
                        "status": "1"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get salesorder item [GET /user/salesorder]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "salesorder_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "salesorder": {
                    "id": 1,
                    "sale_number": "S0001",
                    "customer_id": 3,
                    "qtemplate_id": 0,
                    "date": "15.12.2015.",
                    "exp_date": "15.12.2015.",
                    "payment_term": "15",
                    "sales_person_id": 2,
                    "sales_team_id": 1,
                    "terms_and_conditions": "drtret",
                    "status": "Draft sales order",
                    "total": 1221,
                    "tax_amount": 195.36,
                    "grand_total": 1416.36,
                    "discount": 11.28,
                    "final_price": 289.28,
                    "user_id": 1,
                    "created_at": "2015-12-23 17:12:39",
                    "updated_at": "2015-12-23 17:12:39",
                    "deleted_at": null
                },
                "products": {
                    "product": "product",
                    "description": "description",
                    "quantity": 3,
                    "unit_price": 1.95,
                    "taxes": 1.55,
                    "subtotal": 195.36
                }
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post Sales Order [POST /user/post_sales_order]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "customer_id": "1",
                "date": "2015-11-11",
                "qtemplate_id": "1",
                "payment_term": "term",
                "sales_person_id": "1",
                "sales_team_id": "1",
                "grand_total": "12.5",
                "discount": "10.2",
                "final_price": "10.25",
                "sales_prefix": "S00",
                "sales_start_number": "0",
                "status": "Draft sales order"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit quotation [POST /user/edit_sales_order]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "sales_order_id": "1",
                "customer_id": "1",
                "date": "2015-11-11",
                "qtemplate_id": "1",
                "payment_term": "term",
                "sales_person_id": "1",
                "sales_team_id": "1",
                "grand_total": "12.5",
                "discount": "10.2",
                "final_price": "10.25",
                "status": "Draft sales order"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete quotation [POST /user/delete_sales_order]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "sales_order_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all salesteams [GET /user/salesteams]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "salesteam": [
                    {
                        "id": 1,
                        "salesteam": "Name of team",
                        "target": "111",
                        "invoice_forecast": "1125",
                        "actual_invoice": "205"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get salesteam item [GET /user/salesteam]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "salesteam_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "salesteam": {
                    "id": 1,
                    "salesteam": "testera tim 1",
                    "team_leader": 2,
                    "quotations": false,
                    "leads": false,
                    "opportunities": false,
                    "invoice_target": 15,
                    "invoice_forecast": 22,
                    "actual_invoice": 0,
                    "notes": "dfg fdg dfg",
                    "user_id": 1,
                    "created_at": "2015-12-22 19:47:18",
                    "updated_at": "2015-12-22 19:47:29",
                    "deleted_at": null
                }
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post salesteam [POST /user/post_salesteam]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "salesteam": "Title",
                "invoice_target": "8",
                "invoice_forecast": "1",
                "team_leader": "12",
                "team_members": "1,2,5"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit salesteam [POST /user/edit_salesteam]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "salesteam_id": "1",
                "salesteam": "Title",
                "invoice_target": "8",
                "invoice_forecast": "1",
                "team_leader": "12",
                "team_members": "1,2,5"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete salesteam [POST /user/delete_salesteam]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "salesteam_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all staff [GET /user/staffs]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "staffs": [
                    {
                        "id": 1,
                        "full_name": "product name",
                        "email": "email@email.com",
                        "created_at": "2015-11-11"
                    }
                ]
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get single staff [GET /user/staff]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "staff_id": "33"
            }

+ Response 200 (application/json)
    + Body

            {
                "staff": [
                    {
                        "id": 1,
                        "full_name": "full name",
                        "first_name": "name",
                        "last_name": "last name",
                        "phone_number": "+564514368765",
                        "email": "email@email.com",
                        "created_at": "2015-11-11",
                        "permissions": {
                            "sales_team.read": true,
                            "sales_team.write": true,
                            "leads.read": true,
                            "leads.write": true,
                            "opportunities.read": true,
                            "opportunities.write": true,
                            "logged_calls.read": true,
                            "logged_calls.write": true
                        }
                    }
                ]
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post staff [POST /user/post_staff]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "first_name": "first name",
                "last_name": "last name",
                "email": "email@email.com",
                "password": "1password",
                "permissions": {
                    "sales_team.read": true,
                    "sales_team.write": true,
                    "avatar": "base64_encoded_image"
                }
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit staff [POST /user/edit_staff]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "staff_id": "1",
                "first_name": "first name",
                "last_name": "last name",
                "password": "1password",
                "permissions": {
                    "sales_team.read": true,
                    "sales_team.write": true,
                    "avatar": "base64_encoded_image"
                }
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete staff [POST /user/delete_staff]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "staff_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all tasks [GET /user/tasks]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "tasks": [
                    {
                        "id": 1,
                        "task_from": "full_name",
                        "finished": "0",
                        "task_deadline": "2015-11-11",
                        "task_description": "asasd"
                    }
                ]
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get single task [GET /user/task]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "task_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "task": {
                    "id": 1,
                    "task_from": "full_name",
                    "finished": "0",
                    "task_deadline": "2017-11-11",
                    "task_description": "Lorem ipsum"
                }
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post task [POST /user/post_task]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "user_id": "1",
                "task_description": "asasas",
                "task_deadline": "2016-10-10"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit task [POST /user/edit_task]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "task_id": "1",
                "user_id": "1",
                "task_description": "asasas",
                "task_deadline": "2016-10-10"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete task [POST /user/delete_task]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "task_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get dashboard data [GET /user/dashboard]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "customers": 20,
                "contracts": 12,
                "opportunities": 17,
                "products": 8,
                "opportunity_leads": [
                    {
                        "month": "Feb",
                        "year": "2016",
                        "opportunity": 0,
                        "leads": 0
                    },
                    {
                        "month": "Mar",
                        "year": "2016",
                        "opportunity": 0,
                        "leads": 0
                    },
                    {
                        "month": "Apr",
                        "year": "2016",
                        "opportunity": 0,
                        "leads": 0
                    },
                    {
                        "month": "May",
                        "year": "2016",
                        "opportunity": 0,
                        "leads": 0
                    },
                    {
                        "month": "Jun",
                        "year": "2016",
                        "opportunity": 0,
                        "leads": 0
                    },
                    {
                        "month": "Jul",
                        "year": "2016",
                        "opportunity": 0,
                        "leads": 0
                    },
                    {
                        "month": "Aug",
                        "year": "2016",
                        "opportunity": 0,
                        "leads": 0
                    },
                    {
                        "month": "Sep",
                        "year": "2016",
                        "opportunity": 1,
                        "leads": 0
                    },
                    {
                        "month": "Oct",
                        "year": "2016",
                        "opportunity": 0,
                        "leads": 3
                    },
                    {
                        "month": "Nov",
                        "year": "2016",
                        "opportunity": 0,
                        "leads": 0
                    },
                    {
                        "month": "Dec",
                        "year": "2016",
                        "opportunity": 13,
                        "leads": 3
                    },
                    {
                        "month": "Jan",
                        "year": "2017",
                        "opportunity": 3,
                        "leads": 1
                    }
                ],
                "stages_chart": [
                    {
                        "title": "New",
                        "value": "New",
                        "color": "#4fc1e9",
                        "opprotunities": 0
                    },
                    {
                        "title": "Qualification",
                        "value": "Qualification",
                        "color": "#a0d468",
                        "opprotunities": 0
                    }
                ]
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all permissions [GET /user/permissions]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "permissions": {
                    "id": 1,
                    "task_from": "full_name",
                    "finished": "0",
                    "task_deadline": "2017-11-11",
                    "task_description": "Lorem ipsum"
                }
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all email Templates [GET /user/email_templates]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "email_templates": [
                    {
                        "id": 1,
                        "title": "Title",
                        "text": "Email text"
                    }
                ]
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get single email template [GET /user/email_template]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "email_template_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "email_template": {
                    "id": 1,
                    "title": "Title",
                    "text": "Email text",
                    "user_id": 1,
                    "created_at": "2017-06-20 08:57:01",
                    "updated_at": "2017-06-20 08:57:01",
                    "deleted_at": null
                }
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Post email template [POST /user/post_email_template]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "title": "Title",
                "text": "Email text"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Edit email template [POST /user/edit_email_template]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "email_template_id": "1",
                "title": "Title",
                "text": "Email text"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Delete email template [POST /user/delete_email_template]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "email_template_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Invite staff [POST /user/invite_staff]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "emails": "email@mail.com,email2@mail.com"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Convert opportunity to quotation [POST /user/convert_opportunity_to_quotation]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "opportunity_id": 1
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Convert quotation to sale order [POST /user/convert_quotation_to_sale_order]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "quotation_id": 1
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Convert quotation to invoice [POST /user/convert_quotation_to_invoice]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "quotation_id": 1
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Convert sale order to invoice [POST /user/convert_sale_order_to_invoice]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "sale_order_id": 1
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Send quotation to email [POST /user/send_quotation]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "quotation_id": 1,
                "subject": "subject",
                "recipients": [
                    1,
                    2,
                    3
                ],
                "body": "body"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Send sale order to email [POST /user/send_sale_order]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "sale_order_id": 1,
                "subject": "subject",
                "recipients": [
                    1,
                    2,
                    3
                ],
                "body": "body"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Send invoice to email [POST /user/send_invoice]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "invoice_id": 1,
                "subject": "subject",
                "recipients": [
                    1,
                    2,
                    3
                ],
                "body": "body"
            }

+ Response 200 (application/json)
    + Body

            {
                "success": "success"
            }

+ Response 403 (application/json)
    + Body

            {
                "error": "no_permissions"
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

# Customer [/customer]
Customer endpoints, can be accessed only with role "customer"

## Get all contract [GET /customer/contract]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "company": [
                    {
                        "id": 1,
                        "start_date": "2015-11-12",
                        "end_date": "2015-11-15",
                        "description": "Description",
                        "company": "Company name",
                        "user": "User name"
                    }
                ]
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all invoices [GET /customer/invoices]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "invoices": [
                    {
                        "id": 1,
                        "invoice_number": "I0056",
                        "invoice_date": "2015-11-11",
                        "customer": "Customer Name",
                        "due_date": "2015-11-12",
                        "grand_total": "15.2",
                        "unpaid_amount": "15.2",
                        "status": "Status"
                    }
                ]
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get invoice item [GET /customer/invoice]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "invoice_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "invoice": {
                    "id": 1,
                    "order_id": 0,
                    "customer_id": 3,
                    "sales_person_id": "2",
                    "sales_team_id": 1,
                    "invoice_number": "I0001",
                    "invoice_date": "08.12.2015. 00:00",
                    "due_date": "24.12.2015. 00:00",
                    "payment_term": "10",
                    "status": "Open Invoice",
                    "total": 1221,
                    "tax_amount": 195.36,
                    "grand_total": 1416.36,
                    "discount": 10,
                    "final_price": 1216.36,
                    "unpaid_amount": 1173.06,
                    "user_id": 1,
                    "created_at": "2015-12-23 18:05:35",
                    "updated_at": "2015-12-28 19:21:48",
                    "deleted_at": null
                },
                "products": {
                    "product": "product",
                    "description": "description",
                    "quantity": 3,
                    "unit_price": 1.95,
                    "taxes": 1.55,
                    "subtotal": 195.36
                }
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all quotations [GET /customer/quotation]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "quotations": [
                    {
                        "id": 1,
                        "quotations_number": "Q002",
                        "date": "2015-11-11",
                        "customer": "customer name",
                        "person": "person name",
                        "grand_total": "12",
                        "status": "Draft quotation"
                    }
                ]
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get quotation item [GET /customer/quotation]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "quotation_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "quotation": {
                    "id": 1,
                    "quotations_number": "Q0001",
                    "customer_id": 3,
                    "qtemplate_id": 0,
                    "date": "08.12.2015. 00:00",
                    "exp_date": "30.12.2015.",
                    "payment_term": "10",
                    "sales_person_id": 2,
                    "sales_team_id": 1,
                    "terms_and_conditions": "dff dfg dfg",
                    "status": "Draft Quotation",
                    "total": 333,
                    "tax_amount": 53.28,
                    "grand_total": 386.28,
                    "discount": 11.28,
                    "final_price": 289.28,
                    "user_id": 1,
                    "created_at": "2015-12-23 18:39:12",
                    "updated_at": "2015-12-23 18:39:12",
                    "deleted_at": null
                },
                "products": {
                    "product": "product",
                    "description": "description",
                    "quantity": 3,
                    "unit_price": 1.95,
                    "taxes": 1.55,
                    "subtotal": 195.36
                }
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all sales orders [GET /customer/sales_orders]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "salesorder": [
                    {
                        "id": 1,
                        "sale_number": "S002",
                        "date": "2015-11-11",
                        "customer": "customer name",
                        "person": "sales person name",
                        "grand_total": "12.53",
                        "status": "Draft sales order"
                    }
                ]
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get salesorder item [GET /customer/salesorder]


+ Request (application/json)
    + Body

            {
                "token": "foo",
                "salesorder_id": "1"
            }

+ Response 200 (application/json)
    + Body

            {
                "salesorder": {
                    "id": 1,
                    "sale_number": "S0001",
                    "customer_id": 3,
                    "qtemplate_id": 0,
                    "date": "15.12.2015.",
                    "exp_date": "15.12.2015.",
                    "payment_term": "15",
                    "sales_person_id": 2,
                    "sales_team_id": 1,
                    "terms_and_conditions": "drtret",
                    "status": "Draft sales order",
                    "total": 1221,
                    "tax_amount": 195.36,
                    "grand_total": 1416.36,
                    "discount": 11.28,
                    "final_price": 289.28,
                    "user_id": 1,
                    "created_at": "2015-12-23 17:12:39",
                    "updated_at": "2015-12-23 17:12:39",
                    "deleted_at": null
                },
                "products": {
                    "product": "product",
                    "description": "description",
                    "quantity": 3,
                    "unit_price": 1.95,
                    "taxes": 1.55,
                    "subtotal": 195.36
                }
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get dashboard data [GET /customer/dashboard]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            [
                {
                    "invoices_by_month": [
                        {
                            "month": "Feb",
                            "year": "2016",
                            "invoices": null,
                            "contracts": 0,
                            "opportunity": 0,
                            "leads": 0
                        },
                        {
                            "month": "Mar",
                            "year": "2016",
                            "invoices": null,
                            "contracts": 0,
                            "opportunity": 0,
                            "leads": 0
                        },
                        {
                            "month": "Apr",
                            "year": "2016",
                            "invoices": null,
                            "contracts": 0,
                            "opportunity": 0,
                            "leads": 0
                        },
                        {
                            "month": "May",
                            "year": "2016",
                            "invoices": null,
                            "contracts": 0,
                            "opportunity": 0,
                            "leads": 0
                        },
                        {
                            "month": "Jun",
                            "year": "2016",
                            "invoices": null,
                            "contracts": 0,
                            "opportunity": 0,
                            "leads": 0
                        },
                        {
                            "month": "Jul",
                            "year": "2016",
                            "invoices": null,
                            "contracts": 0,
                            "opportunity": 0,
                            "leads": 0
                        },
                        {
                            "month": "Aug",
                            "year": "2016",
                            "invoices": null,
                            "contracts": 0,
                            "opportunity": 0,
                            "leads": 0
                        },
                        {
                            "month": "Sep",
                            "year": "2016",
                            "invoices": null,
                            "contracts": 0,
                            "opportunity": 0,
                            "leads": 0
                        },
                        {
                            "month": "Oct",
                            "year": "2016",
                            "invoices": null,
                            "contracts": 0,
                            "opportunity": 0,
                            "leads": 0
                        },
                        {
                            "month": "Nov",
                            "year": "2016",
                            "invoices": null,
                            "contracts": 0,
                            "opportunity": 0,
                            "leads": 0
                        },
                        {
                            "month": "Dec",
                            "year": "2016",
                            "invoices": null,
                            "contracts": 0,
                            "opportunity": 0,
                            "leads": 0
                        },
                        {
                            "month": "Jan",
                            "year": "2017",
                            "invoices": null,
                            "contracts": 0,
                            "opportunity": 0,
                            "leads": 0
                        }
                    ]
                }
            ]

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }

## Get all staff [GET /customer/contacts]


+ Request (application/json)
    + Body

            {
                "token": "foo"
            }

+ Response 200 (application/json)
    + Body

            {
                "staffs": [
                    {
                        "id": 1,
                        "full_name": "product name"
                    }
                ]
            }

+ Response 500 (application/json)
    + Body

            {
                "error": "not_valid_data"
            }