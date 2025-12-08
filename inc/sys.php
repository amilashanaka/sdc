<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

include_once 'env.php';




// System configeration
$notification_icons = array(
    'To-Dos' => 'fas fa-boxes',
    'To-Dos1' => 'fas fa-user-md'
);
$notification_icons_json = json_encode($notification_icons); // to javascript


$admin_list_page_element = array(
    'heading' => 'Admin List',
    'new' => 'admin',
    'table' => array(
        'th' => array(
            'User Name',
            'e-mail',
            'Mobile number',
            'Join Date',
            'Action'
        ),
    ),
    'tbl' => 'admins',
    'redirect' => 'admin_list',
    't1' => 'Admin User',
    't2' => 'list',
);



$admin_page_element = array(
    'heading' => 'Document',
    'tabs' => array(
        'tab1' => array(
            'heading' => 'Profile',
            'form_action' => 'data/register_admin.php',
            'inputs' => array(
                'id' => array(
                    'type' => 'h',
                    'value' => '',
                ),
                'action' => array(
                    'type' => 'h',
                    'value' => 'update',
                ),

                'f1' => array(
                    'type' => 'h',
                    'value' => '',
                ),

                'f6' => array(
                    'label' => 'Name',
                    'type' => 'text',
                    'required' => 'required',
                    'class' => 'form-control',
                    'value' => '',
                    'items' => array(),
                    'skip' => false,
                    'div_class' => 'col-lg-12 col-md-12 form-group',
                ),
                'f8' => array(
                    'label' => 'Mobile number',
                    'type' => 'text',
                    'required' => 'required',
                    'class' => 'form-control',
                    'value' => '',
                    'items' => array(),
                    'skip' => false,
                    'div_class' => 'col-lg-12 col-md-12 form-group',
                ),

                'f9' => array(
                    'label' => 'E-mail',
                    'type' => 'email',
                    'required' => 'required',
                    'class' => 'form-control',
                    'value' => '',
                    'items' => array(),
                    'skip' => false,
                    'div_class' => 'col-lg-12 col-md-12 form-group',
                ),

                'f10' => array(
                    'label' => 'Address',
                    'type' => 'text',
                    'required' => 'required',
                    'class' => 'form-control',
                    'value' => '',
                    'items' => array(),
                    'skip' => false,
                    'div_class' => 'col-lg-12 col-md-12 form-group',
                ),

                'f10' => array(
                    'label' => 'National Insurance number',
                    'type' => 'text',
                    'required' => 'required',
                    'class' => 'form-control',
                    'value' => '',
                    'items' => array(),
                    'skip' => false,
                    'div_class' => 'col-lg-12 col-md-12 form-group',
                ),
            )
        ),

        'tab2' => array(
            'heading' => 'Profile',
            'form_action' => 'data/register_admin.php',
            'inputs' => array(
                'id' => array(
                    'type' => 'h',
                    'value' => '',
                ),
                'action' => array(
                    'type' => 'h',
                    'value' => 'reset_pwd',
                ),
                'f1' => array(
                    'type' => 'h',
                    'value' => '',
                ),
                'pwd' => array(
                    'label' => 'Password',
                    'type' => 'password',
                    'required' => 'required',
                    'class' => 'form-control',
                    'value' => '',
                    'items' => array(),
                    'skip' => false,
                    'div_class' => 'col-lg-12 col-md-12 form-group',
                ),

                'pwd_conf' => array(
                    'label' => 'Confirm Password',
                    'type' => 'password',
                    'required' => 'required',
                    'class' => 'form-control',
                    'value' => '',
                    'items' => array(),
                    'skip' => false,
                    'div_class' => 'col-lg-12 col-md-12 form-group',
                ),

            )

        ),


    )



);








$signal_page_element = array(
    'heading' => 'Signals',
    'form_action' => 'data/register_signal.php',
    'inputs' => array(
        'id' => array(
            'type' => 'h',
            'value' => '',
        ),

        'f1' => array(
            'label' => 'Period',
            'type' => 'text',
            'required' => 'required',
            'class' => 'form-control',
            'value' => '',
            'skip' => false,
            'div_class' => 'col-lg-12 col-md-12 form-group',
        ),
        'f2' => array(
            'label' => 'Pips Gained',
            'type' => 'text',
            'required' => 'required',
            'class' => 'form-control',
            'value' => '',
            'skip' => false,
            'div_class' => 'col-lg-12 col-md-12 form-group',
        ),
        'f3' => array(
            'label' => 'Green Trades',
            'type' => 'text',
            'required' => 'required',
            'class' => 'form-control',
            'value' => '',
            'skip' => false,
            'div_class' => 'col-lg-12 col-md-12 form-group',
        ),

        'f4' => array(
            'label' => 'Red Trades',
            'type' => 'text',
            'required' => 'required',
            'class' => 'form-control',
            'value' => '',
            'skip' => false,
            'div_class' => 'col-lg-12 col-md-12 form-group',
        ),


    ),
);

$blog_page_elements = array(
    'heading' => 'Blogs',
    'form_action' => 'data/register_blog.php',
    'inputs' => array(
        'id' => array(
            'type' => 'h',
            'value' => '',
        ),
        'f1' => array(
            'label' => 'Title',
            'type' => 'text',
            'class' => 'form-control',
            'skip' => false,
            'div_class' => 'col-lg-12 col-md-12 form-group',
        ),
        'f2' => array(
            'label' => 'Sub Title',
            'type' => 'text',
            'class' => 'form-control',
            'skip' => false,
            'div_class' => 'col-lg-12 col-md-12 form-group',
        ),

        'f5' => array(
            'label' => 'Content',
            'type' => 'textarea',
            'class' => 'form-control summernote',
            'skip' => false,
            'div_class' => 'col-lg-12 col-md-12 form-group',
        )


    ),
);


//sample

$sample_page_elements = array(
    'heading' => 'Category',
    'inputs' => array(
        'f1' => array(
            'label' => 'Icon',
            'type' => 'image',
            'skip' => true,
        ),
        'f2' => array(
            'label' => 'Category Name',
            'type' => 'text',
            'pattern' => '[A-Z0-9]*',
            'required' => 'required',
            'class' => 'form-control',
            'value' => '',
            'items' => array(),
            'skip' => false,
        ),
        'f3' => array(
            'label' => 'Description',
            'type' => 'textarea',
            'pattern' => '',
            'required' => 'required',
            'class' => 'form-control',
            'value' => '',
            'items' => array(),
            'skip' => false,
            'rows'  => 5,
        ),
        'f4' => array(
            'label' => 'Agree to Terms',
            'type' => 'checkbox',
            'required' => 'required',
            'class' => 'form-check-input',
            'checked' => true,
            'items' => array(),
            'skip' => false,
        ),
        'f5' => array(
            'label' => 'Select Option',
            'type' => 'combobox',
            'required' => 'required',
            'class' => 'form-control',
            'value' => '',
            'items' => array(
                array('value' => '1', 'label' => 'Option 1'),
                array('value' => '2', 'label' => 'Option 2'),
                array('value' => '3', 'label' => 'Option 3'),
            ),
            'skip' => false,
        )
    ),
);




//------------------------------------------------------

$sys = array();

$sys['APP_NAME']                                   = 'TENX ANALYTIX';
$sys['System Name']                                = 'TENX Admin';
$sys['System Section']                             = 'Care Home Management System Admin';
$sys['SYSTEM']                                     = 'ADMINISTRATION PLATFORM';
$sys['telegram']                                   = 'https://t.me/TenXAnalytix';
$sys['facebook']                                   = 'https://www.facebook.com/10XAnalytix/';
$sys['instagram']                                  = 'https://www.instagram.com/10xanalytix/';


// user settings --------------------------------------------------------------------

$sys['user-tab-1']                                  = 'Profile';
$sys['user-tab-2']                                  = 'Referal';
$sys['user-tab-3']                                  = 'Wallet';
$sys['user-tab-4']                                  = 'Invoices';
$sys['user-tab-5']                                  = '';
$sys['user-tab-6']                                  = '';

$sys['user-f1']                                            = 'Email';
$sys['user-f2']                                            = 'Name';
$sys['user-f3']                                            = 'Date of Birth';
$sys['user-f4']                                            = 'Mobile number';
$sys['user-f5']                                            = 'Address';
$sys['user-f6']                                            = 'Region';
$sys['user-f7']                                            = 'Referal';
$sys['user-f8']                                            = 'Referal';
$sys['SM3']                                                = 'Password';

//-----------------------------------------------------------------------------------------


// admin settings --------------------------------------------------------------------


$sys['admin-tab-1']                                            = 'Profile';
$sys['admin-tab-2']                                            = 'Reset Password';
$sys['admin-tab-3']                                            = 'My Clients';
$sys['admin-tab-4']                                            = 'Documents';
$sys['admin-tab-5']                                            = 'Medication';


$sys['admin-f1']                                             = 'Role';
$sys['admin-f2']                                             = 'User Name';
$sys['admin-f3']                                             = 'Password';
$sys['admin-f4']                                             = 'Staff Id';
$sys['admin-f5']                                             = 'pin';
$sys['admin-f6']                                             = 'Name';
$sys['admin-f7']                                             = 'Gender';
$sys['admin-f8']                                             = 'Mobile number';
$sys['admin-f9']                                             = 'e-mail';
$sys['admin-f10']                                            = 'Address';
$sys['admin-f11']                                            = 'National Insurance number';
$sys['admin-f12']                                            = '';
$sys['admin-f13']                                            = '';
$sys['admin-f14']                                            = '';
$sys['admin-f15']                                            = '';
$sys['admin-f16']                                            = '';




//---------- User ----------------------------------------------------------------



$user_field = array();

$user_field['f1']                                            = 'email';
$user_field['f2']                                            = 'password';
$user_field['f3']                                            = 'contact';
$user_field['f4']                                            = 'name'; 
$user_field['f5']                                            = 'Region';
$user_field['f6']                                            = 'address';
$user_field['f7']                                            = 'DOB';
$user_field['f8']                                            = 'password';
$user_field['f9']                                            = 'email-veryfy';
$user_field['f10']                                           = 'otp_verifications';

//---------- Blog ----------------------------------------------------------------

$sys['blog-f1']                                            = 'Title';
$sys['blog-f2']                                            = 'Sub Title';
$sys['blog-f3']                                            = 'image';
$sys['blog-f4']                                            = 'icon';
$sys['blog-f5']                                            = 'Content';
$sys['SM5']                                                = 'Blog';
//------- Logs ----------------------------------------------------------------

$sys['log-f1']                                            = 'Title';
$sys['log-f2']                                            = 'Note';
$sys['log-user']                                            = 'Client';
$sys['log-staff']                                            = 'Staff';

//------- Signals ----------------------------------------------------------------

$sys['sig-f1']                                            = 'Period';
$sys['sig-f2']                                            = 'Pips Gained';
$sys['sig-f3']                                            = 'Green Trades';
$sys['sig-f4']                                            = 'Red Trades';
$sys['SM4']                                               = 'Signals';

$sys['Action']                                           = 'Action';

//------- Slider settings --------------------------------

$sys['slider1-f1']                                            = '  90% of Proven';
$sys['slider1-f2']                                            = ' success rate';
$sys['slider1-f3']                                            = 'World’s most accurate copy trading signal provider for Major Forex Pairs | Gold | US30 | NAS 100';
$sys['slider1-image']                                            = 'assets/images/Slider/1.jpg';
$sys['slider-f4']                                            = 'URL';
$sys['SM6']                                         = 'Slider';