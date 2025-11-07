<?php

namespace App\Http\Controllers\Api;

use App\ApiClasses\Success;
use App\Http\Controllers\Controller;
use App\Models\Settings;
use ModuleConstants;

class SettingsController extends Controller
{
  public function getAppSettings()
  {
    $settings = Settings::first();

    $response = [
      'appVersion' => $settings->app_version,
      'locationUpdateIntervalType' => $settings->m_location_update_time_type == 'seconds' ? 's' : 'm',
      'locationUpdateInterval' => $settings->m_location_update_interval,
      'locationDistanceFilter' => $settings->m_location_update_interval,
      'privacyPolicyUrl' => $settings->privacy_policy_url,
      'currency' => $settings->currency,
      'currencySymbol' => $settings->currency_symbol,
      'distanceUnit' => $settings->distance_unit,
      'countryPhoneCode' => $settings->phone_country_code,
      'supportEmail' => $settings->support_email,
      'supportPhone' => $settings->support_phone,
      'supportWhatsapp' => $settings->support_whatsapp,
      'website' => $settings->website,
      'companyName' => $settings->company_name,
      'companyLogo' => $settings->company_logo ? tenant_asset('images/' . $settings->company_logo) : null,
      'companyAddress' => $settings->company_address,
      'companyPhone' => $settings->company_phone,
      'companyEmail' => $settings->company_email,
      'companyWebsite' => $settings->company_website,
      'companyCountry' => $settings->company_country,
      'companyState' => $settings->company_state,
    ];
    return Success::response($response);
  }

  public function getModuleSettings()
  {
    $availableModules = Settings::first()->available_modules;
    $response = [
      'isProductModuleEnabled' => in_array(ModuleConstants::PRODUCT_ORDER, $availableModules),
      'isTaskModuleEnabled' => in_array(ModuleConstants::TASK_SYSTEM, $availableModules),
      'isNoticeModuleEnabled' => in_array(ModuleConstants::NOTICE_BOARD, $availableModules),
      'isDynamicFormModuleEnabled' => in_array(ModuleConstants::DYNAMIC_FORMS, $availableModules),
      'isExpenseModuleEnabled' => in_array(ModuleConstants::EXPENSE_MANAGEMENT, $availableModules),
      'isLeaveModuleEnabled' => in_array(ModuleConstants::LEAVE_MANAGEMENT, $availableModules),
      'isDocumentModuleEnabled' => in_array(ModuleConstants::DOCUMENT, $availableModules),
      'isChatModuleEnabled' => in_array(ModuleConstants::CHAT_SYSTEM, $availableModules),
      'isLoanModuleEnabled' => in_array(ModuleConstants::LOAN_MANAGEMENT, $availableModules),
      'isAiChatModuleEnabled' => in_array(ModuleConstants::AI_CHATBOT, $availableModules),
      'isPaymentCollectionModuleEnabled' => in_array(ModuleConstants::PAYMENT_COLLECTION, $availableModules),
      'isGeofenceModuleEnabled' => in_array(ModuleConstants::GEOFENCE, $availableModules),
      'isIpBasedAttendanceModuleEnabled' => in_array(ModuleConstants::IP_ADDRESS_ATTENDANCE, $availableModules),
      'isUidLoginModuleEnabled' => in_array(ModuleConstants::UID_LOGIN, $availableModules),
      'isClientVisitModuleEnabled' => in_array(ModuleConstants::CLIENT_VISIT, $availableModules),
      'isOfflineTrackingModuleEnabled' => in_array(ModuleConstants::OFFLINE_TRACKING, $availableModules),
      'isBiometricVerificationModuleEnabled' => false,
      'isQrCodeAttendanceModuleEnabled' => in_array(ModuleConstants::QR_ATTENDANCE, $availableModules),
      'isDynamicQrCodeAttendanceEnabled' => in_array(ModuleConstants::DYNAMIC_QR_ATTENDANCE, $availableModules),
      'isBreakModuleEnabled' => in_array(ModuleConstants::BREAK, $availableModules),
      'isSiteModuleEnabled' => in_array(ModuleConstants::SITE_ATTENDANCE, $availableModules),
      'isDataImportExportModuleEnabled' => in_array(ModuleConstants::DATA_IMPORT_EXPORT, $availableModules),
      'isPayrollModuleEnabled' => in_array(ModuleConstants::PAYROLL, $availableModules),
      'isSalesTargetModuleEnabled' => in_array(ModuleConstants::SALES_TARGET, $availableModules),
      'isDigitalIdCardModuleEnabled' => in_array(ModuleConstants::DIGITAL_ID_CARD, $availableModules),
      'isSosModuleEnabled' => in_array(ModuleConstants::SOS, $availableModules),
      'isApprovalModuleEnabled' => in_array(ModuleConstants::APPROVALS, $availableModules),
      'isRecruitmentModuleEnabled' => in_array(ModuleConstants::RECRUITMENT, $availableModules),
      'isCalendarModuleEnabled' => in_array(ModuleConstants::CALENDAR, $availableModules),
    ];
    return Success::response($response);
  }
}
