<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', ['namespace' => 'App\Http\Controllers\Api'], function ($api) {

    $api->get('/','AuthController@crmCheck');

    $api->post('login', 'AuthController@login');
	$api->post('edit_profile', 'AuthController@editProfile');
	$api->post('password_recovery', 'AuthController@passwordRecovery');
	$api->post('update_password', 'AuthController@updatePassword');
	$api->post('create_profile_invite', 'AuthController@createProfileInvite');

	$api->get('email', 'AuthController@email');
	$api->get('emails', 'AuthController@emails');
	$api->post('post_email', 'AuthController@postEmail');
	$api->post('delete_email', 'AuthController@deleteEmail');
	$api->post('replay_email', 'AuthController@replayEmail');

//routes for user and staff
    $api->group(array('prefix' => 'user', 'middleware' => 'api.user.staff'), function ($api) {

        $api->get('calendar', 'UserController@calendar');

        $api->get('countries', 'UserController@countries');
        $api->get('states', 'UserController@states');
        $api->get('cities', 'UserController@cities');

        $api->get('settings', 'UserController@settings');
        $api->post('update_settings', 'UserController@updateSettings');

        $api->get('calls', 'UserController@calls');
        $api->get('call', 'UserController@call');
        $api->post('post_call', 'UserController@postCall');
        $api->post('edit_call', 'UserController@editCall');
        $api->post('delete_call', 'UserController@deleteCall');

        $api->get('categories', 'UserController@categories');
        $api->get('category', 'UserController@category');
        $api->post('post_category', 'UserController@postCategory');
        $api->post('edit_category', 'UserController@editCategory');
        $api->post('delete_category', 'UserController@deleteCategory');

        $api->get('companies', 'UserController@companies');
        $api->get('company', 'UserController@company');
        $api->post('post_company', 'UserController@postCompany');
        $api->post('edit_company', 'UserController@editCompany');
        $api->post('delete_company', 'UserController@deleteCompany');

        $api->get('contract', 'UserController@contract');
        $api->get('contracts', 'UserController@contracts');
        $api->post('post_contract', 'UserController@postContract');
        $api->post('edit_contract', 'UserController@editContract');
        $api->post('delete_contract', 'UserController@deleteContract');

        $api->get('customer', 'UserController@customer');
        $api->get('customers', 'UserController@customers');
        $api->post('post_customer', 'UserController@postCustomer');
        $api->post('edit_customer', 'UserController@editCustomer');
        $api->post('delete_customer', 'UserController@deleteCustomer');

        $api->get('invoice', 'UserController@invoice');
        $api->get('invoices', 'UserController@invoices');
        $api->post('post_invoice', 'UserController@postInvoice');
        $api->post('edit_invoice', 'UserController@editInvoice');
        $api->post('delete_invoice', 'UserController@deleteInvoice');

        $api->get('invoice_payments', 'UserController@invoicePayments');
        $api->get('invoice_payment', 'UserController@invoicePayment');
        $api->post('post_invoice_payment', 'UserController@postInvoicePayment');

        $api->get('lead_calls', 'UserController@leadCalls');
        $api->get('lead_call', 'UserController@leadCall');
        $api->post('post_lead_call', 'UserController@postLeadCall');
        $api->post('edit_lead_call', 'UserController@editLeadCall');
        $api->post('delete_lead_call', 'UserController@deleteLeadCall');

        $api->get('lead', 'UserController@lead');
        $api->get('leads', 'UserController@leads');
        $api->post('post_lead', 'UserController@postLead');
        $api->post('edit_lead', 'UserController@editLead');
        $api->post('delete_lead', 'UserController@deleteLead');

        $api->get('meeting', 'UserController@meeting');
        $api->get('meetings', 'UserController@meetings');
        $api->post('post_meeting', 'UserController@postMeeting');
        $api->post('edit_meeting', 'UserController@editMeeting');
        $api->post('delete_meeting', 'UserController@deleteMeeting');

        $api->get('opportunity_calls', 'UserController@opportunityCalls');
        $api->get('opportunity_call', 'UserController@opportunityCall');
        $api->post('post_opportunity_call', 'UserController@postOpportunityCall');
        $api->post('edit_opportunity_call', 'UserController@editOpportunityCall');
        $api->post('delete_opportunity_call', 'UserController@deleteOpportunityCall');

        $api->get('opportunities', 'UserController@opportunities');
        $api->get('opportunity', 'UserController@opportunity');
        $api->post('post_opportunity', 'UserController@postOpportunity');
        $api->post('edit_opportunity', 'UserController@editOpportunity');
        $api->post('delete_opportunity', 'UserController@deleteOpportunity');

        $api->get('opportunity_meetings', 'UserController@opportunityMeetings');
        $api->get('opportunity_meeting', 'UserController@opportunityMeeting');
        $api->post('post_opportunity_meeting', 'UserController@postOpportunityMeeting');
        $api->post('edit_opportunity_meeting', 'UserController@editOpportunityMeeting');
        $api->post('delete_opportunity_meeting', 'UserController@deleteOpportunityMeeting');

        $api->get('product', 'UserController@product');
        $api->get('products', 'UserController@products');
        $api->post('post_product', 'UserController@postProduct');
        $api->post('edit_product', 'UserController@editProduct');
        $api->post('delete_product', 'UserController@deleteProduct');

        $api->get('qtemplate', 'UserController@qtemplate');
        $api->get('qtemplates', 'UserController@qtemplates');
        $api->post('post_qtemplate', 'UserController@postQtemplate');
        $api->post('edit_qtemplate', 'UserController@editQtemplate');
        $api->post('delete_qtemplate', 'UserController@deleteQtemplate');

        $api->get('quotation', 'UserController@quotation');
        $api->get('quotations', 'UserController@quotations');
        $api->post('post_quotation', 'UserController@postQuotation');
        $api->post('edit_quotation', 'UserController@editQuotation');
        $api->post('delete_quotation', 'UserController@deleteQuotation');

        $api->get('sales_order', 'UserController@salesOrder');
        $api->get('sales_orders', 'UserController@salesOrders');
        $api->post('post_sales_order', 'UserController@postSalesOrder');
        $api->post('edit_sales_order', 'UserController@editSalesOrder');
        $api->post('delete_sales_order', 'UserController@deleteSalesOrder');

        $api->get('salesteam', 'UserController@salesTeam');
        $api->get('salesteams', 'UserController@salesTeams');
        $api->post('post_salesteam', 'UserController@postSalesTeam');
        $api->post('edit_salesteam', 'UserController@editSalesTeam');
        $api->post('delete_salesteam', 'UserController@deleteSalesTeam');

        $api->get('staffs', 'UserController@staffs');
        $api->get('staff', 'UserController@staff');
        $api->post('post_staff', 'UserController@postStaff');
        $api->post('edit_staff', 'UserController@editStaff');
        $api->post('delete_staff', 'UserController@deleteStaff');

        $api->get('tasks', 'UserController@tasks');
        $api->get('task', 'UserController@task');
        $api->post('post_task', 'UserController@postTask');
        $api->post('edit_task', 'UserController@editTask');
        $api->post('delete_task', 'UserController@deleteTask');

	    $api->get('dashboard', 'UserController@dashboard');
	    $api->get('permissions', 'UserController@permissions');

	    $api->get('email_template', 'UserController@emailTemplate');
	    $api->get('email_templates', 'UserController@emailTemplates');
	    $api->post('post_email_template', 'UserController@postEmailTemplate');
	    $api->post('edit_email_template', 'UserController@editEmailTemplate');
	    $api->post('delete_email_template', 'UserController@deleteEmailTemplate');

	    $api->post('invite_staff', 'UserController@inviteStaff');

	    $api->post('convert_opportunity_to_quotation', 'UserController@convertOpportunityToQuotation');
	    $api->post('convert_quotation_to_sale_order', 'UserController@convertQuotationToSaleOrder');
	    $api->post('convert_quotation_to_invoice', 'UserController@convertQuotationToInvoice');
	    $api->post('convert_sale_order_to_invoice', 'UserController@convertSaleOrderToInvoice');

	    $api->post('send_quotation', 'UserController@sendQuotation');
	    $api->post('send_sale_order', 'UserController@sendSaleOrder');
	    $api->post('send_invoice', 'UserController@sendInvoice');

    });

//routes for customer
    $api->group(array('prefix' => 'customer'), function ($api) {
        $api->get('contract', 'CustomerController@contract');
        $api->get('invoices', 'CustomerController@invoices');
	    $api->get('invoice', 'CustomerController@invoice');
        $api->get('quotations', 'CustomerController@quotations');
	    $api->get('quotation', 'CustomerController@quotation');
        $api->get('sales_orders', 'CustomerController@salesOrders');
	    $api->get('sales_order', 'CustomerController@salesOrder');
	    $api->get('dashboard', 'CustomerController@dashboard');
	    $api->get('contacts', 'CustomerController@contacts');
    });
});
