<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::post('classes/ajaxupdate/', 'ProfileController@ajaxGetClassMedlin');
Route::post('classes/new/ajaxupdate/', 'ProfileController@ajaxGetClassMedlin');
Route::post('classes_in_person/ajaxupdate/', 'ProfileController@ajaxGetClassMedlin');
Route::post('classes_in_person/new/ajaxupdate/', 'ProfileController@ajaxGetClassMedlin');

Route::get('', 'HomeController@index')->name('home');

//CRON
Route::get('cron/check-referral', 'CronController@checkReferral');
Route::get('cron/send-link', 'CronController@sendLinkEmail');
Route::get('cron/active-subscriptions', 'CronController@activeSubscriptions');
Route::get('cron/automated-subscription-reminder', 'CronController@automatedSubscriptionReminder');
Route::get('cron/active-dele-trial', 'CronController@activeDeleTrial');
Route::get('cron/active-prebook', 'CronController@activePrebook');
Route::get('cron/credits', 'CronController@checkCredits');
Route::get('cron/second-payment-immersion', 'CronController@secondPaymentImmersion');
Route::get('cron/second-payment-immersion/{date}', 'CronController@secondPaymentImmersion');
Route::get('cron/remove-users', 'CronController@removeUsers');
Route::get('cron/active-location', 'CronController@activeLocation');
Route::get('cron/scheduled-change', 'CronController@changeScheduledPlans');

// Authentication Routes...
Route::get('signup/provider', 'ProfileController@getProviderSignup')->name('signupprovider');
Route::get('signup/user', 'ProfileController@getUserSignup')->name('usersignup');
Route::post('signup/user', 'ProfileController@saveUserSignup')->name('save_usersignup');
Route::get('login', 'ProfileController@getLogin')->name('login');
Route::post('login', 'ProfileController@postLogin')->name('post_login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout_post');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');
//Route::post('external/registeruser','ExternalController@registerUser')->name("hau_register");
//Route::post('external/updateuser','ExternalController@updateUser')->name("hau_update");

// Password Reset Routes...
Route::get('password/reset', 'ProfileController@getResetPassword')->name("password_reset");
Route::post('password/email', 'ProfileController@sendResetLinkEmail')->name("post_password_reset");
Route::get('password/reset/{token}', 'ProfileController@getResetPasswordToken')->name("password_reset_token");
Route::post('password/reset', 'ProfileController@resetPassword')->name("post_password_reset_token");

//Profile
Route::get('profile', 'ProfileController@getProfile')->middleware("logged")->name('profile');
Route::get('profile/stop_impersonate', 'ProfileController@stopImpersonate')->middleware("logged")->name('stop_impersonate');
Route::get('profile/progress', 'ProfileController@getProgress')->middleware("logged")->middleware('subscribed')->name('profile_progress');
Route::get('profile/zoom_required', 'ProfileController@getZoomFill')->middleware("logged")->name('profile_zoom_email');
Route::post('profile/zoom_required', 'ProfileController@saveZoom')->middleware("logged")->name('save_profile_zoom_email');
Route::get('profile/get-baselang-free', 'ProfileController@getFreeTime')->middleware('logged')->name('referral_page');
Route::get('profile/go-baselang-free', 'ProfileController@cantAfford')->middleware('subscribed')->name('referral_page_cancel');
Route::get('profile/googlecalendar', 'ProfileController@connectGoogleAccount')->middleware("logged")->name('google_account');
Route::get('profile/googlecalendar/link', 'ProfileController@linkGoogleAccount')->middleware("logged")->name('link_google_account');
Route::get('profile/googlecalendar/unlink', 'ProfileController@unlinkGoogleAccount')->middleware("logged")->name('unlink_google_account');
Route::post('profile/save', 'ProfileController@saveProfile')->middleware("logged")->name('save_profile');
Route::post('profile/type/change', 'ProfileController@changeType')->middleware('subscribed')->name('change_type');
Route::get('profile/changerol/{rol_name}', 'ProfileController@changeRol')->middleware('logged')->name('change_rol');
Route::get('feedback/leave_your_feedback', 'ProfileController@getFeedback')->middleware("logged")->name('get_feedback');
Route::post('feedback/post_feedback', 'ProfileController@saveFeedback')->middleware('logged')->name('save_feedback');


//Billing
Route::prefix('billing')->group(function () {
    //Billing
    Route::get('', 'User\BillingController@getBilling')->middleware('logged')->name('billing');
    Route::post('chargebee/session', 'User\BillingController@chargebeeSession')->middleware('logged')->name('chargebee_session');

    Route::get('payments', 'User\BillingController@getPaymentHistory')->middleware('logged')->name('billing_history');
    Route::get('payments/{skip}', 'User\BillingController@getPaymentHistory');
    Route::get('resubscribe', 'User\BillingController@resubscribe')->middleware('logged')->name('resubscribe');

    Route::post('start/now', 'User\BillingController@startNow')->middleware('logged')->name('billing_start_now');
    Route::post('start/date', 'User\BillingController@changeStartDate')->middleware('logged')->name('billing_change_start_date');


    Route::middleware(['subscribed'])->group(function () {
        Route::get('cancel', 'User\BillingController@getCancelSubscription')->name('cancel');
        Route::post('cancel', 'User\BillingController@cancelSubscription');
        Route::get('cancel/undo', 'User\BillingController@cancelUndo')->name('cancel_undo');
        Route::get('cancel/now', 'User\BillingController@cancelNow')->name('cancel_now');

        Route::get('cancel/pause', 'User\BillingController@getCancelAdvice')->name('cancel_advice');
        Route::get('cancel/survey', 'User\BillingController@getCancelSurvey')->name('cancel_survey');
        Route::get('cancel/survey/{reason}', 'User\BillingController@getCancelReason')->name('cancel_reason');
        Route::get('cancel/survey/{reason}/confirm', 'User\BillingController@getCancelConfirm')->name('cancel_confirm');
        Route::post('cancel/survey/{reason}/confirm', 'User\BillingController@getCancelConfirm');

        Route::get('cancel/hourly', 'User\BillingController@enableExtraHourByCancel')->name('cancel_hourly');
        Route::get('cancel/free_time', 'User\BillingController@enableFreeTimeByCancel')->name('cancel_free_time');
        Route::get('cancel/pause/confirm/{reason?}', 'User\BillingController@getCancelPause')->name('cancel_pause');
        Route::get('cancel/take_break', 'User\BillingController@enabletakebreakCancel')->name('change_subscription_hourly');

        Route::get('change/subscription', 'User\BillingController@getChangeSubscription')->name('change_subscription');
        Route::get('change/subscription/{subscription}', 'User\BillingController@getChangeSubscription')->name('change_subscription_preview');
        Route::get('change/subscription/now/{subscription}', 'User\BillingController@changeSubscriptionNow')->name('change_subscription_now');
        Route::get('change/subscription/end/{subscription}', 'User\BillingController@changeSubscriptionEnd')->name('change_subscription_end');

        Route::get('change/location', 'User\BillingController@getChangeLocation')->name('change_location');
        Route::get('change/location/{subscription}', 'User\BillingController@getChangeLocation')->name('change_location_preview');

        Route::get('change/now', 'User\BillingController@changeNow')->name('change_now');
        Route::get('change/cancel', 'User\BillingController@changeCancel')->name('change_cancel');

        Route::get('pause', 'User\BillingController@getPauseAccount')->name('pause_account');
        Route::post('pause', 'User\BillingController@pauseAccount');

        //Credits
        Route::get('credits', 'User\BillingController@getCreditsBuy')->name('credits');
        Route::post('credits/buy', 'User\BillingController@buyCredits')->name('buy_credits');

        //new added by me
        Route::post('change', 'ProfileController@updateSubscription')->name('upgrade_subscription');
        Route::post('updatechargebee', 'ProfileController@updateCardChargebee')->name('billing_update_card');
        Route::get('change_card', 'ProfileController@getChangeCard')->name('change_card');

    });

    Route::get('pause/extend', 'User\BillingController@getPauseExtend')->middleware('logged')->name('pause_extend');
    Route::post('pause/extend', 'User\BillingController@pauseExtend');
    Route::get('pause/undo', 'User\BillingController@pauseUndo')->middleware('logged')->name('pause_undo');
    Route::get('pause/resume', 'User\BillingController@pauseResume')->middleware('logged')->name('pause_resume');
    Route::get('pause/cancel', 'User\BillingController@pauseCancel')->middleware('logged')->name('pause_cancel');

});

//Billing - Prebook
Route::get('billing/prebook', 'ProfileController@getPrebook')->middleware("logged")->name('get_prebook');
Route::post('billing/buy_prebook', 'ProfileController@buyPrebook')->middleware("logged")->name('buy_prebook');
Route::post('billing/upgrade_prebook_gold', 'ProfileController@upgradePrebookGold')->middleware("logged")->name('upgrade_prebook_gold');

//Admin
Route::get('admin/dashboard', 'Admin\AdminController@getAdminDashboard')->middleware("logged")->middleware(['role:admin'])->name('admin_dashboard');
Route::get('admin/load_teachers_favs/{location_id}', 'Admin\AdminController@getTeachersFavs')->middleware("logged")->middleware(['role:admin'])->name('admin_load_teachers_favs');
Route::get('admin', 'Admin\AdminController@getAdmin')->middleware("logged")->middleware(['role:admin'])->name('admin');
Route::get('admin/feedback', 'Admin\AdminController@getFeedback')->middleware("logged")->middleware(['role:admin'])->name('admin_feedback');
Route::get('admin/feedback/get', 'Admin\AdminController@getListFeedback')->middleware("logged")->middleware(['role:admin'])->name('get_admin_feedback');
Route::get('admin/feedback/csv', 'Admin\AdminController@csvFeedback')->middleware("logged")->middleware(['role:admin'])->name('admin_feedback_csv');
Route::get('admin/teachers_favorites_csv', 'Admin\AdminController@csvTeachersFavorites')->middleware("logged")->middleware(['role:admin'])->name('admin_teachers_favorites_csv');
Route::get('admin/history_teachers_favorites_csv', 'Admin\AdminController@csvHistoryTeachersFavorites')->middleware("logged")->middleware(['role:admin'])->name('admin_history_teachers_favorites_csv');
Route::get('admin/subscriptions_status', 'Admin\AdminController@csvSubscriptionsStatus')->middleware("logged")->middleware(['role:admin'])->name('admin_subscriptions_status_csv');

//Admin - Lessons
Route::get('admin/lessons', 'Admin\LessonsController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_lessons');
Route::get('admin/lessons/get', 'Admin\LessonsController@getList')->middleware("logged")->middleware(['role:admin'])->name('get_admin_lessons');
Route::get('admin/lessons/edit/{lesson_id}', 'Admin\LessonsController@getEdit')->middleware("logged")->middleware(['role:admin'])->name('admin_lessons_edit');
Route::get('admin/lessons/trash/{lesson_id}', 'Admin\LessonsController@getTrash')->middleware("logged")->middleware(['role:admin'])->name('admin_lessons_trash');
Route::get('admin/lessons/create', 'Admin\LessonsController@getCreate')->middleware("logged")->middleware(['role:admin'])->name('admin_lessons_create');
Route::post('admin/lessons/removepdf', 'Admin\LessonsController@removePDF')->middleware("logged")->middleware(['role:admin'])->name('admin_remove_pdf');
Route::post('admin/lessons/create', 'Admin\LessonsController@create')->middleware("logged")->middleware(['role:admin'])->name('admin_lessons_create');
Route::post('admin/lessons/edit', 'Admin\LessonsController@update')->middleware("logged")->middleware(['role:admin'])->name('admin_lessons_update');
Route::post('admin/lessons/delete', 'Admin\LessonsController@delete')->middleware("logged")->middleware(['role:admin'])->name('admin_lessons_delete');

//Admin - Levels
Route::get('admin/levels', 'Admin\LevelsController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_levels');
Route::get('admin/levels/get', 'Admin\LevelsController@getList')->middleware("logged")->middleware(['role:admin'])->name('get_admin_levels');
Route::get('admin/levels/edit/{level_id}', 'Admin\LevelsController@getEdit')->middleware("logged")->middleware(['role:admin'])->name('admin_levels_edit');
Route::get('admin/levels/trash/{level_id}', 'Admin\LevelsController@getTrash')->middleware("logged")->middleware(['role:admin'])->name('admin_levels_trash');
Route::get('admin/levels/create', 'Admin\LevelsController@getCreate')->middleware("logged")->middleware(['role:admin'])->name('admin_levels_create');
Route::post('admin/levels/create', 'Admin\LevelsController@create')->middleware("logged")->middleware(['role:admin'])->name('admin_levels_create');
Route::post('admin/levels/edit', 'Admin\LevelsController@update')->middleware("logged")->middleware(['role:admin'])->name('admin_levels_update');
Route::post('admin/levels/delete', 'Admin\LevelsController@delete')->middleware("logged")->middleware(['role:admin'])->name('admin_levels_delete');

//Admin - Users
Route::get('admin/users', 'Admin\UsersController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_users');
Route::get('admin/users/csv', 'Admin\UsersController@csvSummary')->middleware("logged")->middleware(['role:admin'])->name('admin_users_csv');
Route::get('admin/users/get', 'Admin\UsersController@getList')->middleware("logged")->middleware(['role:admin'])->name('get_admin_users');
Route::get('admin/users/impersonate/{user_id}', 'Admin\UsersController@impersonate')->middleware("logged")->middleware(['role:admin'])->name('user_impersonate');
Route::get('admin/users/edit/{user_id}', 'Admin\UsersController@getEdit')->middleware("logged")->middleware(['role:admin'])->name('admin_users_edit');
Route::get('admin/users/trash/{user_id}', 'Admin\UsersController@getTrash')->middleware("logged")->middleware(['role:admin'])->name('admin_users_trash');
Route::get('admin/users/create', 'Admin\UsersController@getCreate')->middleware("logged")->middleware(['role:admin'])->name('admin_users_create');
// GUARDA EL ID DEL 
Route::get('admin/users/{id}/transaction','Admin\UsersController@getCreateZoom')->middleware("logged")->middleware(['role:admin'])->name('admin_users_create_zoom');


Route::get('admin/users/updateSub/{user_id}', 'Admin\UsersController@updateSubscription')->middleware("logged")->middleware(['role:admin'])->name('admin_users_update_subscription');
Route::get('admin/users/cancelSub/{user_id}', 'Admin\UsersController@cancelSubscription')->middleware("logged")->middleware(['role:admin'])->name('admin_users_cancel_subscription');
Route::get('admin/users/cancelSubIme/{user_id}', 'Admin\UsersController@cancelSubscriptionImmediately')->middleware("logged")->middleware(['role:admin'])->name('admin_users_cancel_subscription_immediately');
Route::post('admin/users/addFreeDays', 'Admin\UsersController@addFreeDays')->middleware("logged")->middleware(['role:admin'])->name('admin_users_add_days');
Route::post('admin/users/addElective', 'Admin\UsersController@addElective')->middleware("logged")->middleware(['role:admin'])->name('admin_users_add_elective');
Route::post('admin/users/create', 'Admin\UsersController@create')->middleware("logged")->middleware(['role:admin'])->name('admin_users_create');
Route::post('admin/users/edit', 'Admin\UsersController@update')->middleware("logged")->middleware(['role:admin'])->name('admin_users_update');
Route::post('admin/users/delete', 'Admin\UsersController@delete')->middleware("logged")->middleware(['role:admin'])->name('admin_users_delete');
Route::post('admin/users/add_dele_trial', 'Admin\UsersController@addDeleTrial')->middleware("logged")->middleware(['role:admin'])->name('admin_users_add_dele_trial');
Route::get('admin/users/online_rw_active', 'Admin\UsersController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_users_online_rw_active');
Route::get('admin/users/online_dele_active', 'Admin\UsersController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_users_online_dele_active');
Route::get('admin/users/online_hourly_active', 'Admin\UsersController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_users_online_hourly_active');
Route::get('admin/users/medellin_rw_mo_active', 'Admin\UsersController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_users_medellin_rw_mo_active');
Route::get('admin/users/medellin_rw_wk_active', 'Admin\UsersController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_users_medellin_rw_wk_active');
Route::get('admin/users/medellin_rw_1199_mo_active', 'Admin\UsersController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_users_medellin_rw_1199_mo_active');
Route::get('admin/users/medellin_rw_lite_mo_active', 'Admin\UsersController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_users_medellin_rw_lite_mo_active');
Route::get('admin/users/medellin_dele_mo_active', 'Admin\UsersController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_users_medellin_dele_mo_active');
Route::get('admin/users/medellin_dele_wk_active', 'Admin\UsersController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_users_medellin_dele_wk_active');
Route::get('admin/users/medellin_sm_active', 'Admin\UsersController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_users_medellin_sm_active');
Route::get('admin/users/filter/{type}', 'Admin\UsersController@getListFilter')->middleware("logged")->middleware(['role:admin'])->name('get_admin_users_filter');

//Admin - Cancellations
Route::get('admin/cancellations', 'Admin\CancellationsController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_cancellations');
Route::post('admin/cancellations', 'Admin\CancellationsController@getFilter')->middleware("logged")->middleware(['role:admin'])->name('admin_cancellations_filter');
Route::get('admin/cancellations/csv', 'Admin\CancellationsController@csvSummary')->middleware("logged")->middleware(['role:admin'])->name('admin_cancellations_csv');
Route::get('admin/cancellations/{from}/{till}', 'Admin\CancellationsController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_cancellations_filtered');
Route::get('admin/cancellations/get/{from}/{till}', 'Admin\CancellationsController@getList')->middleware("logged")->middleware(['role:admin'])->name('get_admin_cancellations');

//Admin - Cancellations Table
Route::get('admin/cancellations_table', 'Admin\CancellationsController@getTableIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_cancellations_table');
Route::post('admin/cancellations_table', 'Admin\CancellationsController@getTableFilter')->middleware("logged")->middleware(['role:admin'])->name('admin_cancellations_filter_table');
Route::get('admin/cancellations_table/{from}/{till}', 'Admin\CancellationsController@getTableIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_cancellations_filtered_table');
Route::get('admin/cancellations_table_list/get/{from}/{till}', 'Admin\CancellationsController@getTable')->middleware("logged")->middleware(['role:admin'])->name('get_admin_cancellations_table');

//Admin - Pauses
Route::get('admin/pauses', 'Admin\PausesController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_pauses');
Route::get('admin/pauses/get', 'Admin\PausesController@getList')->middleware("logged")->middleware(['role:admin'])->name('get_admin_pauses');
Route::get('admin/pauses/edit/{pause_id}', 'Admin\PausesController@getEdit')->middleware("logged")->middleware(['role:admin'])->name('admin_pauses_edit');
Route::get('admin/pauses/restart_subscription_now/{pause_id}', 'Admin\PausesController@restartSubscriptionNow')->middleware("logged")->middleware(['role:admin'])->name('admin_restart_subscription_now');
Route::post('admin/pauses/restart_subscription_after', 'Admin\PausesController@restartSubscriptionAfter')->middleware("logged")->middleware(['role:admin'])->name('admin_restart_subscription_after');
Route::get('admin/pauses/pause_undo/{user_id}', 'Admin\PausesController@pauseUndo')->middleware("logged")->middleware(['role:admin'])->name('admin_pause_undo_now');

//Admin - Classes
Route::get('admin/classes', 'Admin\ClassesController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_classes');
Route::post('admin/classes', 'Admin\ClassesController@getFilter')->middleware("logged")->middleware(['role:admin'])->name('admin_classes_filter');
Route::get('admin/classes/{from}/{till}/{teacher}/{student}', 'Admin\ClassesController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_classes_filtered');
Route::get('admin/classes_list/get/{from}/{till}/{teacher}/{student}', 'Admin\ClassesController@getList')->middleware("logged")->middleware(['role:admin'])->name('get_admin_classes');

//Admin - Prebook
Route::get('admin/prebooks/csv', 'Admin\PrebookController@csvSummary')->middleware("logged")->middleware(['role:admin'])->name('admin_prebooks_csv');
Route::get('admin/prebooks', 'Admin\PrebookController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_prebooks');
Route::get('admin/prebooks/get', 'Admin\PrebookController@getList')->middleware("logged")->middleware(['role:admin'])->name('get_admin_prebooks');
Route::get('admin/prebooks/edit/{buy_prebook_id}', 'Admin\PrebookController@getEdit')->middleware("logged")->middleware(['role:admin'])->name('admin_prebooks_edit');
Route::post('admin/prebooks/edit', 'Admin\PrebookController@update')->middleware("logged")->middleware(['role:admin'])->name('admin_prebooks_update');
Route::post('admin/prebooks/cancel', 'Admin\PrebookController@cancelPrebook')->middleware("logged")->middleware(['role:admin'])->name('admin_cancel_prebooks');
Route::get('admin/prebooks/new/{user_id}', 'Admin\PrebookController@getNewPrebook')->middleware("logged")->middleware(['role:admin'])->name('admin_prebooks_new');
Route::get('admin/prebooks/new/{user_id}/{teacher_id}', 'Admin\PrebookController@getNewPrebook')->middleware("logged")->middleware(['role:admin'])->name('admin_prebooks_new_teacher');
Route::get('admin/prebooks/calendar', 'Admin\PrebookController@getCalendar')->middleware("logged")->middleware(['role:admin'])->name('admin_prebooks_all');
Route::get('admin/prebooks/calendar/{user_id}/{teacher_id}', 'Admin\PrebookController@getCalendar')->middleware("logged")->middleware(['role:admin'])->name('admin_prebooks_teacher');
Route::post('admin/prebooks/confirm_prebook', 'Admin\PrebookController@getConfirmPrebook')->middleware("logged")->middleware(['role:admin'])->name('admin_confirm_prebook');
Route::post('admin/prebooks/save', 'Admin\PrebookController@savePrebook')->middleware("logged")->middleware(['role:admin'])->name('admin_save_prebook');
Route::get('admin/prebooks/availability_teachers', 'Admin\PrebookController@getAvailabilityTeachers')->middleware("logged")->middleware(['role:admin'])->name('admin_prebooks_availability_teachers');
Route::get('admin/prebooks/check_availability/{day}', 'Admin\PrebookController@getCheckAvailability')->middleware("logged")->middleware(['role:admin'])->name('admin_prebooks_check_availability');

//Admin - Classes Table
Route::get('admin/classes_table', 'Admin\ClassesController@getTableIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_classes_table');
Route::post('admin/classes_table', 'Admin\ClassesController@getTableFilter')->middleware("logged")->middleware(['role:admin'])->name('admin_classes_filter_table');
Route::get('admin/classes_table/{from}/{till}/{teacher}/{student}', 'Admin\ClassesController@getTableIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_classes_filtered_table');
Route::get('admin/classes_table_list/get/{from}/{till}/{teacher}/{student}', 'Admin\ClassesController@getTable')->middleware("logged")->middleware(['role:admin'])->name('get_admin_classes_table');

//Admin - Log Reader
Route::get('admin/log_reader', 'Admin\AdminController@getLogReader')->middleware("logged")->middleware(['role:admin'])->name('admin_get_log_reader');
Route::get('admin/log_reader/{date}', 'Admin\AdminController@getLogReaderDate')->middleware("logged")->middleware(['role:admin'])->name('admin_get_log_reader_date');

//Admin/Coordinator - Rankings
Route::get('admin/rankings', 'Admin\RankingsController@getRankings')->middleware("logged")->middleware(['role:admin|coordinator'])->name('admin_coordinator_rankings');
Route::get('admin/rankings/get', 'Admin\RankingsController@getTeachersList')->middleware("logged")->middleware(['role:admin|coordinator'])->name('get_teachers_rankings');
Route::get('admin/rankings/get_rankings_csv', 'Admin\RankingsController@csvRankings')->middleware("logged")->middleware(['role:admin|coordinator'])->name('rankings_teachers_csv');
Route::get('admin/rankings/filter/{specific_rating}/{location_id}', 'Admin\RankingsController@getTeachersFilterList')->middleware("logged")->middleware(['role:admin|coordinator'])->name('get_teachers_filterr_rankings');
Route::get('admin/teacher_statistics', 'Admin\RankingsController@teacherStatistics')->middleware("logged")->middleware(['role:admin|coordinator'])->name('admin_teacher_statistics');

//Admin - Locations
Route::get('admin/locations', 'Admin\LocationsController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_locations');
Route::get('admin/locations/get', 'Admin\LocationsController@getList')->middleware("logged")->middleware(['role:admin'])->name('get_admin_locations');
Route::get('admin/locations/create', 'Admin\LocationsController@getCreate')->middleware("logged")->middleware(['role:admin'])->name('admin_locations_create');
Route::post('admin/locations/create', 'Admin\LocationsController@create')->middleware("logged")->middleware(['role:admin'])->name('admin_locations_create');
Route::get('admin/locations/edit/{location_id}', 'Admin\LocationsController@getEdit')->middleware("logged")->middleware(['role:admin'])->name('admin_locations_edit');
Route::post('admin/locations/edit', 'Admin\LocationsController@update')->middleware("logged")->middleware(['role:admin'])->name('admin_locations_update');
Route::get('admin/locations/trash/{location_id}', 'Admin\LocationsController@getTrash')->middleware("logged")->middleware(['role:admin'])->name('admin_locations_trash');
;
Route::post('admin/locations/delete', 'Admin\LocationsController@delete')->middleware("logged")->middleware(['role:admin'])->name('admin_locations_delete');
Route::get('admin/locations/users', 'Admin\LocationsController@getIndexUsers')->middleware("logged")->middleware(['role:admin'])->name('admin_locations_users');
Route::get('admin/locations/users/get', 'Admin\LocationsController@getListUsers')->middleware("logged")->middleware(['role:admin'])->name('get_admin_locations_users');

//Admin - Block Days
Route::get('admin/blocked_days/deleteall', 'Admin\BlockDayController@deleteAll')->name('deleteall');
Route::get('admin/blocked_days/blockdaylogs', 'Admin\BlockDayController@getBlockDayLogs')->middleware("logged")->middleware(['role:admin'])->name('admin_block_day_logs');
Route::get('admin/blocked_days', 'Admin\BlockDayController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_block_day');
Route::get('admin/blocked_days/get', 'Admin\BlockDayController@getList')->middleware("logged")->middleware(['role:admin'])->name('get_admin_block_day');
Route::get('admin/blocked_days/create', 'Admin\BlockDayController@getCreate')->middleware("logged")->middleware(['role:admin'])->name('admin_block_day_create');
Route::post('admin/blocked_days/create', 'Admin\BlockDayController@create')->middleware("logged")->middleware(['role:admin'])->name('admin_block_day_create');
Route::get('admin/blocked_days/edit/{block_day_id}', 'Admin\BlockDayController@getEdit')->middleware("logged")->middleware(['role:admin'])->name('admin_block_day_edit');
Route::post('admin/blocked_days/edit', 'Admin\BlockDayController@update')->middleware("logged")->middleware(['role:admin'])->name('admin_block_day_update');
Route::get('admin/blocked_days/trash/{block_day_id}', 'Admin\BlockDayController@getTrash')->middleware("logged")->middleware(['role:admin'])->name('admin_block_day_trash');
Route::post('admin/blocked_days/delete', 'Admin\BlockDayController@delete')->middleware("logged")->middleware(['role:admin'])->name('admin_block_day_delete');

//Admin - Information Contents
Route::get('admin/information_contents', 'Admin\InformationContentsController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_information_contents');
Route::get('admin/information_contents/get', 'Admin\InformationContentsController@getList')->middleware("logged")->middleware(['role:admin'])->name('get_admin_information_contents');
Route::get('admin/information_contents/create', 'Admin\InformationContentsController@getCreate')->middleware("logged")->middleware(['role:admin'])->name('admin_information_contents_create');
Route::post('admin/information_contents/create', 'Admin\InformationContentsController@create')->middleware("logged")->middleware(['role:admin'])->name('admin_information_contents_create');
Route::get('admin/information_contents/edit/{information_content_id}', 'Admin\InformationContentsController@getEdit')->middleware("logged")->middleware(['role:admin'])->name('admin_information_contents_edit');
Route::post('admin/information_contents/edit', 'Admin\InformationContentsController@update')->middleware("logged")->middleware(['role:admin'])->name('admin_information_contents_update');
Route::get('admin/information_contents/trash/{information_content_id}', 'Admin\InformationContentsController@getTrash')->middleware("logged")->middleware(['role:admin'])->name('admin_information_contents_trash');
;
Route::post('admin/information_contents/delete', 'Admin\InformationContentsController@delete')->middleware("logged")->middleware(['role:admin'])->name('admin_information_contents_delete');

//Admin - Immersions
Route::get('admin/immersions', 'Admin\InmersionsController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_inmersions');
Route::get('admin/immersions/locations/{location_id}', 'Admin\InmersionsController@getCalendar')->middleware("logged")->middleware(['role:admin'])->name('admin_inmersions_locations');

//Admin - Locations
Route::get('admin/locations', 'Admin\LocationsController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_locations');
Route::get('admin/locations/get', 'Admin\LocationsController@getList')->middleware("logged")->middleware(['role:admin'])->name('get_admin_locations');
Route::get('admin/locations/create', 'Admin\LocationsController@getCreate')->middleware("logged")->middleware(['role:admin'])->name('admin_locations_create');
Route::post('admin/locations/create', 'Admin\LocationsController@create')->middleware("logged")->middleware(['role:admin'])->name('admin_locations_create');
Route::get('admin/locations/edit/{location_id}', 'Admin\LocationsController@getEdit')->middleware("logged")->middleware(['role:admin'])->name('admin_locations_edit');
Route::post('admin/locations/edit', 'Admin\LocationsController@update')->middleware("logged")->middleware(['role:admin'])->name('admin_locations_update');
Route::get('admin/locations/trash/{location_id}', 'Admin\LocationsController@getTrash')->middleware("logged")->middleware(['role:admin'])->name('admin_locations_trash');
;
Route::post('admin/locations/delete', 'Admin\LocationsController@delete')->middleware("logged")->middleware(['role:admin'])->name('admin_locations_delete');

//Admin - Inmersions
Route::get('admin/inmersions', 'Admin\InmersionsController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_inmersions');
Route::get('admin/inmersions/locations/{location_id}', 'Admin\InmersionsController@getCalendar')->middleware("logged")->middleware(['role:admin'])->name('admin_inmersions_locations');

//Admin - Free Days
Route::get('admin/free_days', 'Admin\FreeDaysController@getIndex')->middleware("logged")->middleware(['role:admin'])->name('admin_free_days');
Route::post('admin/free_days/add', 'Admin\FreeDaysController@create')->middleware("logged")->middleware(['role:admin'])->name('admin_add_free_days');
Route::post('admin/confirm_free_days', 'Admin\FreeDaysController@confirmFreeDays')->middleware("logged")->middleware(['role:admin'])->name('admin_confirm_free_days');

//Teacher
Route::get('teacher/classes', 'ClassesController@getTeacherClasses')->middleware("logged")->middleware(['role:teacher'])->name('teacher_classes');
Route::get('teacher/classes/user/{id}', 'ClassesController@getTeacherClasses')->middleware("logged")->middleware(['role:teacher'])->name('teacher_classes_student');
Route::get('student/{id}', 'TeachersController@getStudentInfo')->middleware("logged")->middleware(['role:teacher'])->name('teacher_classes_student');
Route::get('teacher/load_classes', 'ClassesController@loadTeacherClasses')->middleware("logged")->middleware(['role:teacher'])->name('teacher_load_classes');

Route::get('teacher/classes/history', 'ClassesController@getTeacherHistory')->middleware("logged")->middleware(['role:teacher'])->name('teacher_history_classes');
Route::get('teacher/classes/history/{page}', 'ClassesController@getTeacherHistory')->middleware("logged")->middleware(['role:teacher'])->name('teacher_history_classes_page');
Route::get('teacher/classes/history/{skip}/{pages}', 'ClassesController@getTeacherHistoryClasses')->middleware("logged")->middleware(['role:teacher'])->name('teacher_history_classes_pages');

Route::get('students', 'StudentsController@getStudents')->middleware("logged")->middleware(['role:teacher'])->name('students');
Route::get('students/get', 'StudentsController@getStudentList')->middleware("logged")->middleware(['role:teacher'])->name('get_students');
Route::post('students/up', 'StudentsController@studentUpLevel')->middleware("logged")->middleware(['role:teacher'])->name('student_up_level');
Route::post('students/down', 'StudentsController@studentDownLevel')->middleware("logged")->middleware(['role:teacher'])->name('student_down_level');
Route::get('students/progress/{user_id}', 'StudentsController@getStudentsProgress')->middleware("logged")->middleware(['role:teacher'])->name('get_students_progress');

// MiniBlog
Route::get('teachers_notes/get/{user_id}/{skip}/{pages}', 'TeachersController@getNotesList')->middleware("logged")->middleware(['role:teacher'])->name('get_teachers_notes');
Route::post('teacher_save_note', 'TeachersController@saveNote')->middleware("logged")->middleware(['role:teacher'])->name('teacher_save_note');
Route::post('teacher_update_note', 'TeachersController@updateNote')->middleware("logged")->middleware(['role:teacher'])->name('teacher_update_note');

// Registration Routes...
Route::get('register', 'ProfileController@getRegister')->name('register');
Route::get('dashboard', 'ProfileController@getDashboard')->middleware('subscribed')->name('dashboard');

//Lessons group
Route::get('lessons', 'LessonController@getLessons')->middleware('subscribed')->name('lessons');
Route::get('lessons/{type}', 'LessonController@getLessons')->middleware('subscribed')->name('lessons_type');
Route::get('lessons/{type}/{level_slug}', 'LessonController@getLevel')->middleware('subscribed')->name('level');
Route::get('lessons/{type}/{level_slug}/{lesson_slug}', 'LessonController@getLesson')->middleware('subscribed')->name('lesson');
Route::post('lesson/changestatus', 'LessonController@saveLesson')->middleware('subscribed')->name('lesson_complete');
Route::post('lesson/homework', 'LessonController@saveHomework')->middleware('subscribed')->name('homework_upload');

Route::get('electives', 'ElectiveController@getLessons')->middleware('subscribed')->name('electives');
Route::get('electives/{level_slug}', 'ElectiveController@getLevel')->middleware('subscribed')->name('elective_level');
Route::get('electives/{level_slug}/{lesson_slug}', 'ElectiveController@getLesson')->middleware('subscribed')->name('elective_lesson');
Route::post('elective/changestatus', 'ElectiveController@saveLesson')->middleware('subscribed')->name('elective_complete');
Route::post('elective/homework', 'ElectiveController@saveHomework')->middleware('subscribed')->name('elective_homework_upload');
Route::get('elective/get/{level_slug}', 'ElectiveController@buyElective')->middleware('subscribed')->name('elective_get');
Route::post('elective/get', 'ElectiveController@chargeElective')->middleware('subscribed')->name('elective_buy');

Route::get('sm_lessons', 'LessonController@getLessons')->middleware('subscribed')->name('sm_lessons');

//Calendar
Route::get('calendar', 'ClassesController@getCalendar')->middleware('subscribed')->name('calendar_all');
Route::get('calendar/{teacher_id}', 'ClassesController@getCalendar')->middleware('subscribed')->name('calendar_teacher');
Route::get('classes/alarm', 'ClassesController@getAlarm')->name('class_alarm');

Route::get('classes_in_person/new', 'ClassesController@getNewClass')->middleware('subscribed')->name('classes_in_person_new');
Route::get('classes_in_person/new/{teacher_id}', 'ClassesController@getNewClass')->middleware('subscribed')->name('classes_user_new_teacher');
Route::get('calendar_in_person', 'ClassesController@getCalendar')->middleware('subscribed')->name('calendar_in_person_all');
Route::get('calendar_in_person/{teacher_id}', 'ClassesController@getCalendar')->middleware('subscribed')->name('calendar_in_person_teacher');

Route::get('classes/new', 'ClassesController@getNewClass')->middleware('subscribed')->name('classes_new');
Route::get('classes/new/{teacher_id}', 'ClassesController@getNewClass')->middleware('subscribed')->name('classes_new_teacher');
Route::get('classes/success', 'ClassesController@getBookedClass')->middleware('subscribed')->name('booked_classes');
Route::get('classes', 'ClassesController@getClasses')->middleware('subscribed')->name('classes');
Route::get('classes/ics', 'ClassesController@getICS')->middleware('subscribed')->name('ics_classes');
Route::get('classes/history', 'ClassesController@getHistory')->middleware('subscribed')->name('history_classes');
Route::get('classes/history/{page}', 'ClassesController@getHistory')->middleware('subscribed')->name('history_classes_page');
Route::get('classes/history/{skip}/{pages}', 'ClassesController@getHistoryClasses')->middleware('subscribed')->name('history_classes_pages');
Route::post('classes/choose_teacher', 'ClassesController@getChooseTeacher')->middleware('subscribed')->name('choose_teacher');
Route::post('classes/confirm_classes', 'ClassesController@getConfirmClasses')->middleware('subscribed')->name('confirm_classes');
Route::post('classes/book', 'ClassesController@saveClasses')->middleware('subscribed')->name('save_classes');
Route::post('classes/cancel', 'ClassesController@cancelClass')->middleware('subscribed')->name('cancel_classes');

//Credits
//Route::get('credits', 'ProfileController@getCreditsBuy')->middleware('subscribed')->name('credits');
//Route::post('credits/buy', 'ProfileController@buyCredits')->middleware('subscribed')->name('buy_credits');

//Teachers
Route::get('teachers', 'TeachersController@getTeachers')->middleware('subscribed')->name('teachers');
Route::post('teachers/get', 'TeachersController@getTeacherList')->middleware('subscribed')->name('get_teachers');
Route::post('teachers/evaluate', 'TeachersController@saveTeacherEvaluation')->middleware('subscribed')->name('evaluate_teachers');
Route::post('teachers/favorite', 'TeachersController@saveTeacherFavorite')->middleware('subscribed')->name('favorite_teachers');

Route::get('teachers_school', 'TeachersController@getTeachers')->middleware('subscribed')->name('teachers_school');
Route::post('teachers_school/get', 'TeachersController@getTeacherList')->middleware('subscribed')->name('get_teachers_school');

//Prebook
Route::get('prebook', 'PrebookController@getPrebook')->middleware('subscribed')->name('prebook');
Route::get('prebook/new', 'PrebookController@getNewPrebook')->middleware('subscribed')->name('prebook_new');
Route::get('prebook/new/{teacher_id}', 'PrebookController@getNewPrebook')->middleware('subscribed')->name('prebook_new_teacher');
Route::get('prebook/calendar', 'PrebookController@getCalendar')->middleware('subscribed')->name('prebook_all');
Route::get('prebook/calendar/{teacher_id}', 'PrebookController@getCalendar')->middleware('subscribed')->name('prebook_teacher');
Route::post('prebook/confirm_prebook', 'PrebookController@getConfirmPrebook')->middleware('subscribed')->name('confirm_prebook');
Route::post('prebook/save', 'PrebookController@savePrebook')->middleware('subscribed')->name('save_prebook');
Route::get('prebook/success', 'PrebookController@getBookedPrebook')->middleware('subscribed')->name('booked_prebook');
Route::post('prebook/cancel', 'PrebookController@cancelPrebook')->middleware('subscribed')->name('cancel_prebook');
Route::get('prebook/prebook_availability', 'PrebookController@getPrebookAvailability')->middleware('subscribed')->name('get_prebook_availability');
Route::get('prebook/prebook_availability/{teacher_id}', 'PrebookController@getPrebookAvailability')->middleware('subscribed')->name('get_prebook_availability_teacher');
Route::get('prebook/read_prebook', 'ProfileController@saveReadPrebook')->middleware('subscribed')->name('read_prebook');

//Immersion
Route::get('immersion/new/{location}', 'InmersionController@getInmersion')->name('inmersion');
Route::get('immersion/new/{location}/{code}', 'InmersionController@getInmersion')->name('inmersion_code');
Route::post('immersion/pick_your_teacher', 'InmersionController@postCalendar')->name('pick_your_teacher');
Route::post('immersion/your_basic_info', 'InmersionController@postCalendarTeacher')->name('your_basic_info');
Route::post('immersion/immersion_login', 'InmersionController@inmersionLogin')->name('inmersion_login');
Route::post('immersion/immersion_create_account', 'InmersionController@inmersionCreateAccount')->name('inmersion_create_account');
Route::post('immersion/pay_deposit', 'InmersionController@postLogged')->name('pay_deposit');

Route::post('immersion/pay_immersion', 'InmersionController@payInmersion')->middleware("logged")->name('pay_inmersion');
Route::post('immersion/update_card_immersion', 'InmersionController@updateCardInmersion')->middleware("logged")->name('update_card_inmersion');
Route::post('immersion/successful_immersion', 'InmersionController@successfulInmersion')->middleware("logged")->name('successful_inmersion');
Route::get('immersion/dashboard_immersion', 'InmersionController@dashboardInmersion')->middleware("logged")->name('dashboard_inmersion');
Route::get('immersion/city_information', 'InmersionController@getCityInformation')->middleware("logged")->name('city_information');
Route::get('immersion/city_information/{info_slug}', 'InmersionController@getInformation')->middleware("logged")->name('get_information');
Route::get('immersion/city_information/{info_slug}/{info_slug_content}', 'InmersionController@getInformationContent')->middleware("logged")->name('get_information_content');

//Chargebee
Route::post('chargebee/session', 'ProfileController@chargebeeSession')->name('chargebee_session');
