<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\HooksViewer;

class HooksViewer extends \Piwik\Plugin
{
    public function registerEvents()
    {
        /*
        $eventNames = [
            "Access.Capability.addCapabilities",
            "Access.Capability.filterCapabilities",
            "Access.modifyUserAccess",
            "Actions.addActionTypes",
            "Actions.Archiving.addActionMetrics",
            "Actions.getCustomActionDimensionFieldsAndJoins",
            //"API.$pluginName.$methodName",
            //"API.$pluginName.$methodName.end",
            "API.addGlossaryItems",
            //"API.DocumentationGenerator.$token",
            "API.getPagesComparisonsDisabledFor",
            "API.getReportMetadata.end",
            "API.Request.authenticate",
            "API.Request.dispatch",
            "API.Request.dispatch.end",
            "API.Request.intercept",
            "ArchiveProcessor.ComputeNbUniques.getIdSites",
            "ArchiveProcessor.getArchive",
            "ArchiveProcessor.Parameters.getIdSites",
            "ArchiveProcessor.shouldAggregateFromRawData",
            "Archiver.addRecordBuilders",
            "Archiver.filterRecordBuilders",
            "Archiving.getIdSitesToArchiveWhenNoVisits",
            "Archiving.getIdSitesToMarkArchivesAsInvalidated",
            "Archiving.getIdSitesToMarkArchivesAsInvalidated",
            "Archiving.makeNewArchiverObject",
            "AssetManager.addStylesheets",
            "AssetManager.filterMergedJavaScripts",
            "AssetManager.filterMergedJavaScripts",
            "AssetManager.filterMergedJavaScripts",
            "AssetManager.filterMergedStylesheets",
            "AssetManager.getJavaScriptFiles",
            "AssetManager.getStylesheetFiles",
            "Category.addSubcategories",
            "Changes.filterChanges",
            "CliMulti.supportsAsync",
            "Config.badConfigurationFile",
            "Config.beforeSave",
            "Config.NoConfigurationFile",
            "Console.filterCommands",
            //"Controller.$module.$action",
            //"Controller.$module.$action.end",
            "Controller.triggerAdminNotifications",
            "Core.configFileChanged",
            "Core.configFileDeleted",
            "Core.configFileSanityCheckFailed",
            "CoreAdminHome.customLogoChanged",
            "CoreUpdater.update.end",
            "CronArchive.archiveSingleSite.finish",
            "CronArchive.archiveSingleSite.start",
            "CronArchive.end",
            "CronArchive.filterWebsiteIds",
            "CronArchive.getIdSitesNotUsingTracker",
            "CronArchive.init.finish",
            "CronArchive.init.start",
            "CustomJsTracker.manipulateJsTracker",
            "CustomJsTracker.shouldAddTrackerFile",
            "CustomJsTracker.trackerJsChanged",
            "CustomJsTracker.trackerJsChanged",
            "CustomJsTracker.updateTracker",
            "Db.cannotConnectToDb",
            "Db.getActionReferenceColumnsByTable",
            "Db.getDatabaseConfig",
            "Db.getTablesInstalled",
            "Dimension.addDimensions",
            "Dimension.filterDimensions",
            "Environment.bootstrapped",
            "Feedback.showQuestionBanner",
            "Filesystem.allCachesCleared",
            "FrontController.modifyErrorPage",
            "Http.sendHttpRequest",
            "Http.sendHttpRequest.end",
            "Insights.addReportToOverview",
            "Installation.defaultSettingsForm.init",
            "Installation.defaultSettingsForm.submit",
            "LanguagesManager.getAvailableLanguages",
            "Live.addProfileSummaries",
            "Live.addVisitorDetails",
            "Live.API.getIdSitesString",
            "Live.filterProfileSummaries",
            "Live.filterVisitorDetails",
            "Live.makeNewVisitorObject",
            "Login.authenticate",
            "Login.authenticate",
            "Login.authenticate.failed",
            "Login.authenticate.failed",
            "Login.authenticate.processSuccessfulSession.end",
            "Login.authenticate.processSuccessfulSession.end",
            "Login.authenticate.processSuccessfulSession.end",
            "Login.authenticate.processSuccessfulSession.end",
            "Login.authenticate.processSuccessfulSession.end",
            "Login.authenticate.processSuccessfulSession.end",
            "Login.authenticate.processSuccessfulSession.end",
            "Login.authenticate.processSuccessfulSession.end",
            "Login.authenticate.processSuccessfulSession.end",
            "Login.authenticate.processSuccessfulSession.end",
            "Login.authenticate.processSuccessfulSession.end",
            "Login.authenticate.processSuccessfulSession.end",
            "Login.authenticate.processSuccessfulSession.end",
            "Login.authenticate.processSuccessfulSession.end",
            "Login.authenticate.successful",
            "Login.authenticate.successful",
            "Login.logout",
            "Login.resetPassword.cancelled",
            "Login.resetPassword.confirmed",
            "Login.resetPassword.initiated",
            "Login.userRequiresPasswordConfirmation",
            "Mail.send",
            "Mail.shouldSend",
            "MeasurableSettings.updated",
            "Metric.addComputedMetrics",
            "Metric.addMetrics",
            "Metric.filterMetrics",
            "Metrics.getDefaultMetricDocumentationTranslations",
            "Metrics.getDefaultMetricSemanticTypes",
            "Metrics.getDefaultMetricTranslations",
            "Metrics.getEvolutionUnit",
            "Metrics.isLowerValueBetter",
            "MobileMessaging.deletePhoneNumber",
            "MultiSites.filterRowsForTotalsCalculation",
            "MultiSites.filterSites",
            "Platform.initialized",
            "Platform.initialized",
            "PluginManager.pluginActivated",
            "PluginManager.pluginActivated",
            "PluginManager.pluginDeactivated",
            "PluginManager.pluginInstalled",
            "PluginManager.pluginUninstalled",
            "PrivacyManager.deleteDataSubjects",
            "PrivacyManager.deleteLogsOlderThan",
            "PrivacyManager.exportDataSubjects",
            "PrivacyManager.shouldIgnoreDnt",
            "Provider.getCleanHostname",
            "Referrer.addSearchEngineUrls",
            "Referrer.addSocialUrls",
            "Report.addReports",
            "Report.filterReports",
            "Report.unsubscribe",
            "Request.dispatch",
            "Request.dispatch",
            "Request.dispatch",
            "Request.dispatch",
            "Request.dispatch",
            "Request.dispatch",
            "Request.dispatch.end",
            "Request.dispatch.end",
            "Request.dispatchCoreAndPluginUpdatesScreen",
            "Request.getRenamedModuleAndAction",
            "Request.initAuthenticationObject",
            "Request.initAuthenticationObject",
            "Request.initAuthenticationObject",
            "Request.initAuthenticationObject",
            "Request.initAuthenticationObject",
            "Request.shouldDisablePostProcessing",
            "ScheduledReports.allowMultipleReports",
            "ScheduledReports.getRendererInstance",
            "ScheduledReports.getReportFormats",
            "ScheduledReports.getReportMetadata",
            "ScheduledReports.getReportParameters",
            "ScheduledReports.getReportRecipients",
            "ScheduledReports.getReportTypes",
            "ScheduledReports.processReports",
            "ScheduledReports.sendReport",
            "ScheduledReports.validateReportParameters",
            "ScheduledTasks.execute",
            "ScheduledTasks.execute.end",
            "ScheduledTasks.shouldExecuteTask",
            "Segment.addSegments",
            "Segment.filterSegments",
            "SegmentEditor.deactivate",
            "SegmentEditor.update",
            "Segments.getKnownSegmentsToArchiveAllSites",
            "Segments.getKnownSegmentsToArchiveForSite",
            "SEO.getMetricsProviders",
            "SitesManager.addSite.end",
            "SitesManager.deleteSite.end",
            "SitesManager.getImageTrackingCode",
            "SitesManager.getMessagesToWarnOnSiteRemoval",
            "SitesManager.shouldPerformEmptySiteCheck",
            "System.addSystemSummaryItems",
            "System.filterSystemSummaryItems",
            "SystemSettings.updated",
            "TagManager.addTags",
            "TagManager.addTriggers",
            "TagManager.addVariables",
            "TagManager.containerFileChanged",
            "TagManager.containerFileDeleted",
            "TagManager.deleteContainer.end",
            "TagManager.deleteContainerTag.end",
            "TagManager.deleteContainerTrigger.end",
            "TagManager.deleteContainerVariable.end",
            "TagManager.deleteContainerVersion.end",
            "TagManager.filterTags",
            "TagManager.filterTriggers",
            "TagManager.filterVariables",
            "TagManager.pauseContainerTag.end",
            "TagManager.regenerateContainerReleases",
            "TagManager.resumeContainerTag.end",
            "Template.afterCustomVariablesReport",
            "Template.afterGDPROverviewIntro",
            "Template.afterReferrerTypeReport",
            "Template.beforeGoalListActionsBody",
            "Template.beforeGoalListActionsHead",
            "Template.endGoalEditTable",
            "Template.jsGlobalVariables",
            "Template.jsGlobalVariables",
            "Template.jsGlobalVariables",
            "Template.jsGlobalVariables",
            "Template.loginCancelResetPasswordContent",
            //"Template.siteWithoutDataTab. . $obj::getId() . .content",
            //"Template.siteWithoutDataTab. . $obj::getId() . .others",
            "Tour.filterChallenges",
            "Tracker.Cache.getSiteAttributes",
            "Tracker.detectReferrerSearchEngine",
            "Tracker.detectReferrerSocialNetwork",
            "Tracker.end",
            "Tracker.end",
            "Tracker.getDatabaseConfig",
            "Tracker.getJavascriptCode",
            "Tracker.isExcludedVisit",
            "Tracker.makeNewVisitObject",
            "Tracker.PageUrl.getQueryParametersToExclude",
            "Tracker.Request.getIdSite",
            "Tracker.setTrackerCacheGeneral",
            "TrackingSpamPrevention.banIp",
            "Translate.getClientSideTranslationKeys",
            "TwoFactorAuth.disabled",
            "TwoFactorAuth.enabled",
            "TwoFactorAuth.requiresTwoFactorAuthentication",
            "Updater.componentInstalled",
            "Updater.componentUninstalled",
            "Updater.componentUpdated",
            "User.isNotAuthorized",
            "UserSettings.updated",
            "UsersManager.addUser.end",
            "UsersManager.checkPassword",
            "UsersManager.deleteUser",
            "UsersManager.getDefaultDates",
            "UsersManager.inviteUser.accepted",
            "UsersManager.inviteUser.declined",
            "UsersManager.inviteUser.end",
            "UsersManager.inviteUser.generateInviteLinkToken",
            "UsersManager.inviteUser.resendInvite",
            "UsersManager.removeSiteAccess",
            "UsersManager.removeSiteAccess",
            "UsersManager.updateUser.end",
            "ViewDataTable.configure",
            "ViewDataTable.configure.end",
            "ViewDataTable.filterViewDataTable",
            "Visualization.beforeRender",
            "Widget.addWidgetConfigs",
            "Widget.filterWidgets",
            "Widgetize.shouldEmbedIframeEmpty",
        ];
        */

        $functionMap = [
            "Access.Capability.addCapabilities" => "AccessCapabilityAddCapabilities",
            "Access.Capability.filterCapabilities" => "AccessCapabilityFilterCapabilities",
            "Access.modifyUserAccess" => "AccessModifyUserAccess",
            "Actions.addActionTypes" => "ActionsAddActionTypes",
            "Actions.Archiving.addActionMetrics" => "ActionsArchivingAddActionMetrics",
            "Actions.getCustomActionDimensionFieldsAndJoins" => "ActionsGetCustomActionDimensionFieldsAndJoins",
            //"API.addGlossaryItems" => "APIAddGlossaryItems",
            //"API.getPagesComparisonsDisabledFor" => "APIGetPagesComparisonsDisabledFor",
            //"API.getReportMetadata.end" => "APIGetReportMetadataEnd",
            //"API.Request.authenticate" => "APIRequestAuthenticate",
            //"API.Request.dispatch" => "APIRequestDispatch",
            //"API.Request.dispatch.end" => "APIRequestDispatchEnd",
            //"API.Request.intercept" => "APIRequestIntercept",
            "ArchiveProcessor.ComputeNbUniques.getIdSites" => "ArchiveProcessorComputeNbUniquesGetIdSites",
            "ArchiveProcessor.getArchive" => "ArchiveProcessorGetArchive",
            "ArchiveProcessor.Parameters.getIdSites" => "ArchiveProcessorParametersGetIdSites",
            "ArchiveProcessor.shouldAggregateFromRawData" => "ArchiveProcessorShouldAggregateFromRawData",
            "Archiver.addRecordBuilders" => "ArchiverAddRecordBuilders",
            "Archiver.filterRecordBuilders" => "ArchiverFilterRecordBuilders",
            "Archiving.getIdSitesToArchiveWhenNoVisits" => "ArchivingGetIdSitesToArchiveWhenNoVisits",
            "Archiving.getIdSitesToMarkArchivesAsInvalidated" => "ArchivingGetIdSitesToMarkArchivesAsInvalidated",
            "Archiving.makeNewArchiverObject" => "ArchivingMakeNewArchiverObject",
            "AssetManager.addStylesheets" => "AssetManagerAddStylesheets",
            "AssetManager.filterMergedJavaScripts" => "AssetManagerFilterMergedJavaScripts",
            "AssetManager.filterMergedStylesheets" => "AssetManagerFilterMergedStylesheets",
            "AssetManager.getJavaScriptFiles" => "AssetManagerGetJavaScriptFiles",
            "AssetManager.getStylesheetFiles" => "AssetManagerGetStylesheetFiles",
            //"Category.addSubcategories" => "CategoryAddSubcategories",
            //"Changes.filterChanges" => "ChangesFilterChanges",
            "CliMulti.supportsAsync" => "CliMultiSupportsAsync",
            "Config.badConfigurationFile" => "ConfigBadConfigurationFile",
            "Config.beforeSave" => "ConfigBeforeSave",
            "Config.NoConfigurationFile" => "ConfigNoConfigurationFile",
            //"Console.filterCommands" => "ConsoleFilterCommands",
            "Controller.triggerAdminNotifications" => "ControllerTriggerAdminNotifications",
            "Core.configFileChanged" => "CoreConfigFileChanged",
            "Core.configFileDeleted" => "CoreConfigFileDeleted",
            "Core.configFileSanityCheckFailed" => "CoreConfigFileSanityCheckFailed",
            "CoreAdminHome.customLogoChanged" => "CoreAdminHomeCustomLogoChanged",
            "CoreUpdater.update.end" => "CoreUpdaterUpdateEnd",
            "CronArchive.archiveSingleSite.finish" => "CronArchiveArchiveSingleSiteFinish",
            "CronArchive.archiveSingleSite.start" => "CronArchiveArchiveSingleSiteStart",
            "CronArchive.end" => "CronArchiveEnd",
            "CronArchive.filterWebsiteIds" => "CronArchiveFilterWebsiteIds",
            "CronArchive.getIdSitesNotUsingTracker" => "CronArchiveGetIdSitesNotUsingTracker",
            "CronArchive.init.finish" => "CronArchiveInitFinish",
            "CronArchive.init.start" => "CronArchiveInitStart",
            "CustomJsTracker.manipulateJsTracker" => "CustomJsTrackerManipulateJsTracker",
            "CustomJsTracker.shouldAddTrackerFile" => "CustomJsTrackerShouldAddTrackerFile",
            "CustomJsTracker.trackerJsChanged" => "CustomJsTrackerTrackerJsChanged",
            "CustomJsTracker.updateTracker" => "CustomJsTrackerUpdateTracker",
            "Db.cannotConnectToDb" => "DbCannotConnectToDb",
            "Db.getActionReferenceColumnsByTable" => "DbGetActionReferenceColumnsByTable",
            //"Db.getDatabaseConfig" => "DbGetDatabaseConfig",
            "Db.getTablesInstalled" => "DbGetTablesInstalled",
            //"Dimension.addDimensions" => "DimensionAddDimensions",
            //"Dimension.filterDimensions" => "DimensionFilterDimensions",
            "Environment.bootstrapped" => "EnvironmentBootstrapped",
            "Feedback.showQuestionBanner" => "FeedbackShowQuestionBanner",
            //"Filesystem.allCachesCleared" => "FilesystemAllCachesCleared",
            "FrontController.modifyErrorPage" => "FrontControllerModifyErrorPage",
            //"Http.sendHttpRequest" => "HttpSendHttpRequest",
            "Http.sendHttpRequest.end" => "HttpSendHttpRequestEnd",
            "Insights.addReportToOverview" => "InsightsAddReportToOverview",
            "Installation.defaultSettingsForm.init" => "InstallationDefaultSettingsFormInit",
            "Installation.defaultSettingsForm.submit" => "InstallationDefaultSettingsFormSubmit",
            //"LanguagesManager.getAvailableLanguages" => "LanguagesManagerGetAvailableLanguages",
            "Live.addProfileSummaries" => "LiveAddProfileSummaries",
            "Live.addVisitorDetails" => "LiveAddVisitorDetails",
            "Live.API.getIdSitesString" => "LiveAPIGetIdSitesString",
            "Live.filterProfileSummaries" => "LiveFilterProfileSummaries",
            "Live.filterVisitorDetails" => "LiveFilterVisitorDetails",
            "Live.makeNewVisitorObject" => "LiveMakeNewVisitorObject",
            "Login.authenticate" => "LoginAuthenticate",
            "Login.authenticate.failed" => "LoginAuthenticateFailed",
            "Login.authenticate.processSuccessfulSession.end" => "LoginAuthenticateProcessSuccessfulSessionEnd",
            "Login.authenticate.successful" => "LoginAuthenticateSuccessful",
            "Login.logout" => "LoginLogout",
            "Login.resetPassword.cancelled" => "LoginResetPasswordCancelled",
            "Login.resetPassword.confirmed" => "LoginResetPasswordConfirmed",
            "Login.resetPassword.initiated" => "LoginResetPasswordInitiated",
            //"Login.userRequiresPasswordConfirmation" => "LoginUserRequiresPasswordConfirmation",
            "Mail.send" => "MailSend",
            "Mail.shouldSend" => "MailShouldSend",
            "MeasurableSettings.updated" => "MeasurableSettingsUpdated",
            //"Metric.addComputedMetrics" => "MetricAddComputedMetrics",
            //"Metric.addMetrics" => "MetricAddMetrics",
            //"Metric.filterMetrics" => "MetricFilterMetrics",
            //"Metrics.getDefaultMetricDocumentationTranslations" => "MetricsGetDefaultMetricDocumentationTranslations",
            //"Metrics.getDefaultMetricSemanticTypes" => "MetricsGetDefaultMetricSemanticTypes",
            //"Metrics.getDefaultMetricTranslations" => "MetricsGetDefaultMetricTranslations",
            //"Metrics.getEvolutionUnit" => "MetricsGetEvolutionUnit",
            "Metrics.isLowerValueBetter" => "MetricsIsLowerValueBetter",
            "MobileMessaging.deletePhoneNumber" => "MobileMessagingDeletePhoneNumber",
            "MultiSites.filterRowsForTotalsCalculation" => "MultiSitesFilterRowsForTotalsCalculation",
            "MultiSites.filterSites" => "MultiSitesFilterSites",
            //"Platform.initialized" => "PlatformInitialized",
            //"PluginManager.pluginActivated" => "PluginManagerPluginActivated",
            //"PluginManager.pluginDeactivated" => "PluginManagerPluginDeactivated",
            //"PluginManager.pluginInstalled" => "PluginManagerPluginInstalled",
            //"PluginManager.pluginUninstalled" => "PluginManagerPluginUninstalled",
            "PrivacyManager.deleteDataSubjects" => "PrivacyManagerDeleteDataSubjects",
            "PrivacyManager.deleteLogsOlderThan" => "PrivacyManagerDeleteLogsOlderThan",
            "PrivacyManager.exportDataSubjects" => "PrivacyManagerExportDataSubjects",
            "PrivacyManager.shouldIgnoreDnt" => "PrivacyManagerShouldIgnoreDnt",
            "Provider.getCleanHostname" => "ProviderGetCleanHostname",
            "Referrer.addSearchEngineUrls" => "ReferrerAddSearchEngineUrls",
            "Referrer.addSocialUrls" => "ReferrerAddSocialUrls",
            //"Report.addReports" => "ReportAddReports",
            //"Report.filterReports" => "ReportFilterReports",
            "Report.unsubscribe" => "ReportUnsubscribe",
            //"Request.dispatch" => "RequestDispatch",
            //"Request.dispatch.end" => "RequestDispatchEnd",
            //"Request.dispatchCoreAndPluginUpdatesScreen" => "RequestDispatchCoreAndPluginUpdatesScreen",
            //"Request.getRenamedModuleAndAction" => "RequestGetRenamedModuleAndAction",
            //"Request.initAuthenticationObject" => "RequestInitAuthenticationObject",
            //"Request.shouldDisablePostProcessing" => "RequestShouldDisablePostProcessing",
            "ScheduledReports.allowMultipleReports" => "ScheduledReportsAllowMultipleReports",
            "ScheduledReports.getRendererInstance" => "ScheduledReportsGetRendererInstance",
            "ScheduledReports.getReportFormats" => "ScheduledReportsGetReportFormats",
            "ScheduledReports.getReportMetadata" => "ScheduledReportsGetReportMetadata",
            "ScheduledReports.getReportParameters" => "ScheduledReportsGetReportParameters",
            "ScheduledReports.getReportRecipients" => "ScheduledReportsGetReportRecipients",
            "ScheduledReports.getReportTypes" => "ScheduledReportsGetReportTypes",
            "ScheduledReports.processReports" => "ScheduledReportsProcessReports",
            "ScheduledReports.sendReport" => "ScheduledReportsSendReport",
            "ScheduledReports.validateReportParameters" => "ScheduledReportsValidateReportParameters",
            "ScheduledTasks.execute" => "ScheduledTasksExecute",
            "ScheduledTasks.execute.end" => "ScheduledTasksExecuteEnd",
            "ScheduledTasks.shouldExecuteTask" => "ScheduledTasksShouldExecuteTask",
            "Segment.addSegments" => "SegmentAddSegments",
            "Segment.filterSegments" => "SegmentFilterSegments",
            "SegmentEditor.deactivate" => "SegmentEditorDeactivate",
            "SegmentEditor.update" => "SegmentEditorUpdate",
            "Segments.getKnownSegmentsToArchiveAllSites" => "SegmentsGetKnownSegmentsToArchiveAllSites",
            "Segments.getKnownSegmentsToArchiveForSite" => "SegmentsGetKnownSegmentsToArchiveForSite",
            "SEO.getMetricsProviders" => "SEOGetMetricsProviders",
            "SitesManager.addSite.end" => "SitesManagerAddSiteEnd",
            "SitesManager.deleteSite.end" => "SitesManagerDeleteSiteEnd",
            //"SitesManager.getImageTrackingCode" => "SitesManagerGetImageTrackingCode",
            "SitesManager.getMessagesToWarnOnSiteRemoval" => "SitesManagerGetMessagesToWarnOnSiteRemoval",
            "SitesManager.shouldPerformEmptySiteCheck" => "SitesManagerShouldPerformEmptySiteCheck",
            "System.addSystemSummaryItems" => "SystemAddSystemSummaryItems",
            "System.filterSystemSummaryItems" => "SystemFilterSystemSummaryItems",
            "SystemSettings.updated" => "SystemSettingsUpdated",
            //"TagManager.addTags" => "TagManagerAddTags",
            //"TagManager.addTriggers" => "TagManagerAddTriggers",
            //"TagManager.addVariables" => "TagManagerAddVariables",
            "TagManager.containerFileChanged" => "TagManagerContainerFileChanged",
            "TagManager.containerFileDeleted" => "TagManagerContainerFileDeleted",
            "TagManager.deleteContainer.end" => "TagManagerDeleteContainerEnd",
            "TagManager.deleteContainerTag.end" => "TagManagerDeleteContainerTagEnd",
            "TagManager.deleteContainerTrigger.end" => "TagManagerDeleteContainerTriggerEnd",
            "TagManager.deleteContainerVariable.end" => "TagManagerDeleteContainerVariableEnd",
            "TagManager.deleteContainerVersion.end" => "TagManagerDeleteContainerVersionEnd",
            //"TagManager.filterTags" => "TagManagerFilterTags",
            //"TagManager.filterTriggers" => "TagManagerFilterTriggers",
            //"TagManager.filterVariables" => "TagManagerFilterVariables",
            "TagManager.pauseContainerTag.end" => "TagManagerPauseContainerTagEnd",
            "TagManager.regenerateContainerReleases" => "TagManagerRegenerateContainerReleases",
            "TagManager.resumeContainerTag.end" => "TagManagerResumeContainerTagEnd",
            "Template.afterCustomVariablesReport" => "TemplateAfterCustomVariablesReport",
            "Template.afterGDPROverviewIntro" => "TemplateAfterGDPROverviewIntro",
            "Template.afterReferrerTypeReport" => "TemplateAfterReferrerTypeReport",
            "Template.beforeGoalListActionsBody" => "TemplateBeforeGoalListActionsBody",
            "Template.beforeGoalListActionsHead" => "TemplateBeforeGoalListActionsHead",
            "Template.endGoalEditTable" => "TemplateEndGoalEditTable",
            //"Template.jsGlobalVariables" => "TemplateJsGlobalVariables",
            "Tour.filterChallenges" => "TourFilterChallenges",
            //"Tracker.Cache.getSiteAttributes" => "TrackerCacheGetSiteAttributes",
            "Tracker.detectReferrerSearchEngine" => "TrackerDetectReferrerSearchEngine",
            "Tracker.detectReferrerSocialNetwork" => "TrackerDetectReferrerSocialNetwork",
            "Tracker.end" => "TrackerEnd",
            "Tracker.getDatabaseConfig" => "TrackerGetDatabaseConfig",
            //"Tracker.getJavascriptCode" => "TrackerGetJavascriptCode",
            "Tracker.isExcludedVisit" => "TrackerIsExcludedVisit",
            "Tracker.makeNewVisitObject" => "TrackerMakeNewVisitObject",
            "Tracker.PageUrl.getQueryParametersToExclude" => "TrackerPageUrlGetQueryParametersToExclude",
            "Tracker.Request.getIdSite" => "TrackerRequestGetIdSite",
            "Tracker.setTrackerCacheGeneral" => "TrackerSetTrackerCacheGeneral",
            "TrackingSpamPrevention.banIp" => "TrackingSpamPreventionBanIp",
            "Translate.getClientSideTranslationKeys" => "TranslateGetClientSideTranslationKeys",
            "TwoFactorAuth.disabled" => "TwoFactorAuthDisabled",
            "TwoFactorAuth.enabled" => "TwoFactorAuthEnabled",
            //"TwoFactorAuth.requiresTwoFactorAuthentication" => "TwoFactorAuthRequiresTwoFactorAuthentication",
            "Updater.componentInstalled" => "UpdaterComponentInstalled",
            "Updater.componentUninstalled" => "UpdaterComponentUninstalled",
            "Updater.componentUpdated" => "UpdaterComponentUpdated",
            "User.isNotAuthorized" => "UserIsNotAuthorized",
            "UserSettings.updated" => "UserSettingsUpdated",
            "UsersManager.addUser.end" => "UsersManagerAddUserEnd",
            "UsersManager.checkPassword" => "UsersManagerCheckPassword",
            "UsersManager.deleteUser" => "UsersManagerDeleteUser",
            "UsersManager.getDefaultDates" => "UsersManagerGetDefaultDates",
            "UsersManager.inviteUser.accepted" => "UsersManagerInviteUserAccepted",
            "UsersManager.inviteUser.declined" => "UsersManagerInviteUserDeclined",
            "UsersManager.inviteUser.end" => "UsersManagerInviteUserEnd",
            "UsersManager.inviteUser.generateInviteLinkToken" => "UsersManagerInviteUserGenerateInviteLinkToken",
            "UsersManager.inviteUser.resendInvite" => "UsersManagerInviteUserResendInvite",
            "UsersManager.removeSiteAccess" => "UsersManagerRemoveSiteAccess",
            "UsersManager.updateUser.end" => "UsersManagerUpdateUserEnd",
            "ViewDataTable.configure" => "ViewDataTableConfigure",
            "ViewDataTable.configure.end" => "ViewDataTableConfigureEnd",
            "ViewDataTable.filterViewDataTable" => "ViewDataTableFilterViewDataTable",
            "Visualization.beforeRender" => "VisualizationBeforeRender",
            //"Widget.addWidgetConfigs" => "WidgetAddWidgetConfigs",
            //"Widget.filterWidgets" => "WidgetFilterWidgets",
            "Widgetize.shouldEmbedIframeEmpty" => "WidgetizeShouldEmbedIframeEmpty"
        ];

        return [
            ...$functionMap,
        ];
    }


    public function displayHooksViewerItem($hookName, $args)
    {
        echo'<div class="hv-event">' . $hookName . '</div>';
    }


    /**
     * Handle event values
     */


    function AccessCapabilityAddCapabilities()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Access.Capability.addCapabilities', $args);
        return $args;
    }

    function AccessCapabilityFilterCapabilities()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Access.Capability.filterCapabilities', $args);
        return $args;
    }

    function AccessModifyUserAccess()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Access.modifyUserAccess', $args);
        return $args;
    }

    function ActionsAddActionTypes()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Actions.addActionTypes', $args);
        return $args;
    }

    function ActionsArchivingAddActionMetrics()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Actions.Archiving.addActionMetrics', $args);
        return $args;
    }

    function ActionsGetCustomActionDimensionFieldsAndJoins()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Actions.getCustomActionDimensionFieldsAndJoins', $args);
        return $args;
    }

    function APIAddGlossaryItems()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('API.addGlossaryItems', $args);
        return $args;
    }

    function APIGetPagesComparisonsDisabledFor()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('API.getPagesComparisonsDisabledFor', $args);
        return $args;
    }

    function APIGetReportMetadataEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('API.getReportMetadata.end', $args);
        return $args;
    }

    function APIRequestAuthenticate()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('API.Request.authenticate', $args);
        return $args;
    }

    function APIRequestDispatch()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('API.Request.dispatch', $args);
        return $args;
    }

    function APIRequestDispatchEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('API.Request.dispatch.end', $args);
        return $args;
    }

    function APIRequestIntercept()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('API.Request.intercept', $args);
        return $args;
    }

    function ArchiveProcessorComputeNbUniquesGetIdSites()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ArchiveProcessor.ComputeNbUniques.getIdSites', $args);
        return $args;
    }

    function ArchiveProcessorGetArchive()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ArchiveProcessor.getArchive', $args);
        return $args;
    }

    function ArchiveProcessorParametersGetIdSites()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ArchiveProcessor.Parameters.getIdSites', $args);
        return $args;
    }

    function ArchiveProcessorShouldAggregateFromRawData()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ArchiveProcessor.shouldAggregateFromRawData', $args);
        return $args;
    }

    function ArchiverAddRecordBuilders()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Archiver.addRecordBuilders', $args);
        return $args;
    }

    function ArchiverFilterRecordBuilders()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Archiver.filterRecordBuilders', $args);
        return $args;
    }

    function ArchivingGetIdSitesToArchiveWhenNoVisits()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Archiving.getIdSitesToArchiveWhenNoVisits', $args);
        return $args;
    }

    function ArchivingGetIdSitesToMarkArchivesAsInvalidated()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Archiving.getIdSitesToMarkArchivesAsInvalidated', $args);
        return $args;
    }

    function ArchivingMakeNewArchiverObject()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Archiving.makeNewArchiverObject', $args);
        return $args;
    }

    function AssetManagerAddStylesheets()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('AssetManager.addStylesheets', $args);
        return $args;
    }

    function AssetManagerFilterMergedJavaScripts()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('AssetManager.filterMergedJavaScripts', $args);
        return $args;
    }

    function AssetManagerFilterMergedStylesheets()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('AssetManager.filterMergedStylesheets', $args);
        return $args;
    }

    function AssetManagerGetJavaScriptFiles()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('AssetManager.getJavaScriptFiles', $args);
        return $args;
    }

    function AssetManagerGetStylesheetFiles(&$files)
    {
        $files[] = "plugins/HooksViewer/stylesheets/style.less";
        $this->displayHooksViewerItem('AssetManager.getStylesheetFiles', $files);
    }

    function CategoryAddSubcategories()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Category.addSubcategories', $args);
        return $args;
    }

    function ChangesFilterChanges()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Changes.filterChanges', $args);
        return $args;
    }

    function CliMultiSupportsAsync()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('CliMulti.supportsAsync', $args);
        return $args;
    }

    function ConfigBadConfigurationFile()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Config.badConfigurationFile', $args);
        return $args;
    }

    function ConfigBeforeSave()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Config.beforeSave', $args);
        return $args;
    }

    function ConfigNoConfigurationFile()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Config.NoConfigurationFile', $args);
        return $args;
    }

    function ConsoleFilterCommands()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Console.filterCommands', $args);
        return $args;
    }

    function ControllerTriggerAdminNotifications()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Controller.triggerAdminNotifications', $args);
        return $args;
    }

    function CoreConfigFileChanged()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Core.configFileChanged', $args);
        return $args;
    }

    function CoreConfigFileDeleted()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Core.configFileDeleted', $args);
        return $args;
    }

    function CoreConfigFileSanityCheckFailed()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Core.configFileSanityCheckFailed', $args);
        return $args;
    }

    function CoreAdminHomeCustomLogoChanged()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('CoreAdminHome.customLogoChanged', $args);
        return $args;
    }

    function CoreUpdaterUpdateEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('CoreUpdater.update.end', $args);
        return $args;
    }

    function CronArchiveArchiveSingleSiteFinish()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('CronArchive.archiveSingleSite.finish', $args);
        return $args;
    }

    function CronArchiveArchiveSingleSiteStart()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('CronArchive.archiveSingleSite.start', $args);
        return $args;
    }

    function CronArchiveEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('CronArchive.end', $args);
        return $args;
    }

    function CronArchiveFilterWebsiteIds()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('CronArchive.filterWebsiteIds', $args);
        return $args;
    }

    function CronArchiveGetIdSitesNotUsingTracker()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('CronArchive.getIdSitesNotUsingTracker', $args);
        return $args;
    }

    function CronArchiveInitFinish()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('CronArchive.init.finish', $args);
        return $args;
    }

    function CronArchiveInitStart()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('CronArchive.init.start', $args);
        return $args;
    }

    function CustomJsTrackerManipulateJsTracker()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('CustomJsTracker.manipulateJsTracker', $args);
        return $args;
    }

    function CustomJsTrackerShouldAddTrackerFile()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('CustomJsTracker.shouldAddTrackerFile', $args);
        return $args;
    }

    function CustomJsTrackerTrackerJsChanged()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('CustomJsTracker.trackerJsChanged', $args);
        return $args;
    }

    function CustomJsTrackerUpdateTracker()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('CustomJsTracker.updateTracker', $args);
        return $args;
    }

    function DbCannotConnectToDb()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Db.cannotConnectToDb', $args);
        return $args;
    }

    function DbGetActionReferenceColumnsByTable()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Db.getActionReferenceColumnsByTable', $args);
        return $args;
    }

    function DbGetDatabaseConfig()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Db.getDatabaseConfig', $args);
        return $args;
    }

    function DbGetTablesInstalled()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Db.getTablesInstalled', $args);
        return $args;
    }

    function DimensionAddDimensions()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Dimension.addDimensions', $args);
        return $args;
    }

    function DimensionFilterDimensions()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Dimension.filterDimensions', $args);
        return $args;
    }

    function EnvironmentBootstrapped()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Environment.bootstrapped', $args);
        return $args;
    }

    function FeedbackShowQuestionBanner()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Feedback.showQuestionBanner', $args);
        return $args;
    }

    function FilesystemAllCachesCleared()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Filesystem.allCachesCleared', $args);
        return $args;
    }

    function FrontControllerModifyErrorPage()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('FrontController.modifyErrorPage', $args);
        return $args;
    }

    function HttpSendHttpRequest()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Http.sendHttpRequest', $args);
        return $args;
    }

    function HttpSendHttpRequestEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Http.sendHttpRequest.end', $args);
        return $args;
    }

    function InsightsAddReportToOverview()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Insights.addReportToOverview', $args);
        return $args;
    }

    function InstallationDefaultSettingsFormInit()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Installation.defaultSettingsForm.init', $args);
        return $args;
    }

    function InstallationDefaultSettingsFormSubmit()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Installation.defaultSettingsForm.submit', $args);
        return $args;
    }

    function LanguagesManagerGetAvailableLanguages()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('LanguagesManager.getAvailableLanguages', $args);
        return $args;
    }

    function LiveAddProfileSummaries()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Live.addProfileSummaries', $args);
        return $args;
    }

    function LiveAddVisitorDetails()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Live.addVisitorDetails', $args);
        return $args;
    }

    function LiveAPIGetIdSitesString()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Live.API.getIdSitesString', $args);
        return $args;
    }

    function LiveFilterProfileSummaries()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Live.filterProfileSummaries', $args);
        return $args;
    }

    function LiveFilterVisitorDetails()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Live.filterVisitorDetails', $args);
        return $args;
    }

    function LiveMakeNewVisitorObject()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Live.makeNewVisitorObject', $args);
        return $args;
    }

    function LoginAuthenticate()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Login.authenticate', $args);
        return $args;
    }

    function LoginAuthenticateFailed()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Login.authenticate.failed', $args);
        return $args;
    }

    function LoginAuthenticateProcessSuccessfulSessionEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Login.authenticate.processSuccessfulSession.end', $args);
        return $args;
    }

    function LoginAuthenticateSuccessful()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Login.authenticate.successful', $args);
        return $args;
    }

    function LoginLogout()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Login.logout', $args);
        return $args;
    }

    function LoginResetPasswordCancelled()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Login.resetPassword.cancelled', $args);
        return $args;
    }

    function LoginResetPasswordConfirmed()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Login.resetPassword.confirmed', $args);
        return $args;
    }

    function LoginResetPasswordInitiated()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Login.resetPassword.initiated', $args);
        return $args;
    }

    function LoginUserRequiresPasswordConfirmation()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Login.userRequiresPasswordConfirmation', $args);
        return $args;
    }

    function MailSend()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Mail.send', $args);
        return $args;
    }

    function MailShouldSend()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Mail.shouldSend', $args);
        return $args;
    }

    function MeasurableSettingsUpdated()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('MeasurableSettings.updated', $args);
        return $args;
    }

    function MetricAddComputedMetrics()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Metric.addComputedMetrics', $args);
        return $args;
    }

    function MetricAddMetrics()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Metric.addMetrics', $args);
        return $args;
    }

    function MetricFilterMetrics()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Metric.filterMetrics', $args);
        return $args;
    }

    function MetricsGetDefaultMetricDocumentationTranslations()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Metrics.getDefaultMetricDocumentationTranslations', $args);
        return $args;
    }

    function MetricsGetDefaultMetricSemanticTypes()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Metrics.getDefaultMetricSemanticTypes', $args);
        return $args;
    }

    function MetricsGetDefaultMetricTranslations()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Metrics.getDefaultMetricTranslations', $args);
        return $args;
    }

    function MetricsGetEvolutionUnit()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Metrics.getEvolutionUnit', $args);
        return $args;
    }

    function MetricsIsLowerValueBetter()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Metrics.isLowerValueBetter', $args);
        return $args;
    }

    function MobileMessagingDeletePhoneNumber()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('MobileMessaging.deletePhoneNumber', $args);
        return $args;
    }

    function MultiSitesFilterRowsForTotalsCalculation()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('MultiSites.filterRowsForTotalsCalculation', $args);
        return $args;
    }

    function MultiSitesFilterSites()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('MultiSites.filterSites', $args);
        return $args;
    }

    function PlatformInitialized()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Platform.initialized', $args);
        return $args;
    }

    function PluginManagerPluginActivated()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('PluginManager.pluginActivated', $args);
        return $args;
    }

    function PluginManagerPluginDeactivated()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('PluginManager.pluginDeactivated', $args);
        return $args;
    }

    function PluginManagerPluginInstalled()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('PluginManager.pluginInstalled', $args);
        return $args;
    }

    function PluginManagerPluginUninstalled()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('PluginManager.pluginUninstalled', $args);
        return $args;
    }

    function PrivacyManagerDeleteDataSubjects()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('PrivacyManager.deleteDataSubjects', $args);
        return $args;
    }

    function PrivacyManagerDeleteLogsOlderThan()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('PrivacyManager.deleteLogsOlderThan', $args);
        return $args;
    }

    function PrivacyManagerExportDataSubjects()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('PrivacyManager.exportDataSubjects', $args);
        return $args;
    }

    function PrivacyManagerShouldIgnoreDnt()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('PrivacyManager.shouldIgnoreDnt', $args);
        return $args;
    }

    function ProviderGetCleanHostname()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Provider.getCleanHostname', $args);
        return $args;
    }

    function ReferrerAddSearchEngineUrls()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Referrer.addSearchEngineUrls', $args);
        return $args;
    }

    function ReferrerAddSocialUrls()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Referrer.addSocialUrls', $args);
        return $args;
    }

    function ReportAddReports()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Report.addReports', $args);
        return $args;
    }

    function ReportFilterReports()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Report.filterReports', $args);
        return $args;
    }

    function ReportUnsubscribe()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Report.unsubscribe', $args);
        return $args;
    }

    function RequestDispatch()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Request.dispatch', $args);
        return $args;
    }

    function RequestDispatchEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Request.dispatch.end', $args);
        return $args;
    }

    function RequestDispatchCoreAndPluginUpdatesScreen()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Request.dispatchCoreAndPluginUpdatesScreen', $args);
        return $args;
    }

    function RequestGetRenamedModuleAndAction()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Request.getRenamedModuleAndAction', $args);
        return $args;
    }

    function RequestInitAuthenticationObject()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Request.initAuthenticationObject', $args);
        return $args;
    }

    function RequestShouldDisablePostProcessing()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Request.shouldDisablePostProcessing', $args);
        return $args;
    }

    function ScheduledReportsAllowMultipleReports()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ScheduledReports.allowMultipleReports', $args);
        return $args;
    }

    function ScheduledReportsGetRendererInstance()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ScheduledReports.getRendererInstance', $args);
        return $args;
    }

    function ScheduledReportsGetReportFormats()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ScheduledReports.getReportFormats', $args);
        return $args;
    }

    function ScheduledReportsGetReportMetadata()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ScheduledReports.getReportMetadata', $args);
        return $args;
    }

    function ScheduledReportsGetReportParameters()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ScheduledReports.getReportParameters', $args);
        return $args;
    }

    function ScheduledReportsGetReportRecipients()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ScheduledReports.getReportRecipients', $args);
        return $args;
    }

    function ScheduledReportsGetReportTypes()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ScheduledReports.getReportTypes', $args);
        return $args;
    }

    function ScheduledReportsProcessReports()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ScheduledReports.processReports', $args);
        return $args;
    }

    function ScheduledReportsSendReport()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ScheduledReports.sendReport', $args);
        return $args;
    }

    function ScheduledReportsValidateReportParameters()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ScheduledReports.validateReportParameters', $args);
        return $args;
    }

    function ScheduledTasksExecute()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ScheduledTasks.execute', $args);
        return $args;
    }

    function ScheduledTasksExecuteEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ScheduledTasks.execute.end', $args);
        return $args;
    }

    function ScheduledTasksShouldExecuteTask()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ScheduledTasks.shouldExecuteTask', $args);
        return $args;
    }

    function SegmentAddSegments()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Segment.addSegments', $args);
        return $args;
    }

    function SegmentFilterSegments()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Segment.filterSegments', $args);
        return $args;
    }

    function SegmentEditorDeactivate()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('SegmentEditor.deactivate', $args);
        return $args;
    }

    function SegmentEditorUpdate()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('SegmentEditor.update', $args);
        return $args;
    }

    function SegmentsGetKnownSegmentsToArchiveAllSites()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Segments.getKnownSegmentsToArchiveAllSites', $args);
        return $args;
    }

    function SegmentsGetKnownSegmentsToArchiveForSite()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Segments.getKnownSegmentsToArchiveForSite', $args);
        return $args;
    }

    function SEOGetMetricsProviders()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('SEO.getMetricsProviders', $args);
        return $args;
    }

    function SitesManagerAddSiteEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('SitesManager.addSite.end', $args);
        return $args;
    }

    function SitesManagerDeleteSiteEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('SitesManager.deleteSite.end', $args);
        return $args;
    }

    function SitesManagerGetImageTrackingCode()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('SitesManager.getImageTrackingCode', $args);
        return $args;
    }

    function SitesManagerGetMessagesToWarnOnSiteRemoval()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('SitesManager.getMessagesToWarnOnSiteRemoval', $args);
        return $args;
    }

    function SitesManagerShouldPerformEmptySiteCheck()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('SitesManager.shouldPerformEmptySiteCheck', $args);
        return $args;
    }

    function SystemAddSystemSummaryItems()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('System.addSystemSummaryItems', $args);
        return $args;
    }

    function SystemFilterSystemSummaryItems()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('System.filterSystemSummaryItems', $args);
        return $args;
    }

    function SystemSettingsUpdated()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('SystemSettings.updated', $args);
        return $args;
    }

    function TagManagerAddTags()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.addTags', $args);
        return $args;
    }

    function TagManagerAddTriggers()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.addTriggers', $args);
        return $args;
    }

    function TagManagerAddVariables()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.addVariables', $args);
        return $args;
    }

    function TagManagerContainerFileChanged()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.containerFileChanged', $args);
        return $args;
    }

    function TagManagerContainerFileDeleted()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.containerFileDeleted', $args);
        return $args;
    }

    function TagManagerDeleteContainerEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.deleteContainer.end', $args);
        return $args;
    }

    function TagManagerDeleteContainerTagEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.deleteContainerTag.end', $args);
        return $args;
    }

    function TagManagerDeleteContainerTriggerEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.deleteContainerTrigger.end', $args);
        return $args;
    }

    function TagManagerDeleteContainerVariableEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.deleteContainerVariable.end', $args);
        return $args;
    }

    function TagManagerDeleteContainerVersionEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.deleteContainerVersion.end', $args);
        return $args;
    }

    function TagManagerFilterTags()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.filterTags', $args);
        return $args;
    }

    function TagManagerFilterTriggers()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.filterTriggers', $args);
        return $args;
    }

    function TagManagerFilterVariables()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.filterVariables', $args);
        return $args;
    }

    function TagManagerPauseContainerTagEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.pauseContainerTag.end', $args);
        return $args;
    }

    function TagManagerRegenerateContainerReleases()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.regenerateContainerReleases', $args);
        return $args;
    }

    function TagManagerResumeContainerTagEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TagManager.resumeContainerTag.end', $args);
        return $args;
    }

    function TemplateAfterCustomVariablesReport()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Template.afterCustomVariablesReport', $args);
        return $args;
    }

    function TemplateAfterGDPROverviewIntro()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Template.afterGDPROverviewIntro', $args);
        return $args;
    }

    function TemplateAfterReferrerTypeReport()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Template.afterReferrerTypeReport', $args);
        return $args;
    }

    function TemplateBeforeGoalListActionsBody()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Template.beforeGoalListActionsBody', $args);
        return $args;
    }

    function TemplateBeforeGoalListActionsHead()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Template.beforeGoalListActionsHead', $args);
        return $args;
    }

    function TemplateEndGoalEditTable()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Template.endGoalEditTable', $args);
        return $args;
    }

    function TemplateJsGlobalVariables()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Template.jsGlobalVariables', $args);
        return $args;
    }

    function TemplateLoginCancelResetPasswordContent()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Template.loginCancelResetPasswordContent', $args);
        return $args;
    }

    function TourFilterChallenges()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Tour.filterChallenges', $args);
        return $args;
    }

    function TrackerCacheGetSiteAttributes()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Tracker.Cache.getSiteAttributes', $args);
        return $args;
    }

    function TrackerDetectReferrerSearchEngine()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Tracker.detectReferrerSearchEngine', $args);
        return $args;
    }

    function TrackerDetectReferrerSocialNetwork()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Tracker.detectReferrerSocialNetwork', $args);
        return $args;
    }

    function TrackerEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Tracker.end', $args);
        return $args;
    }

    function TrackerGetDatabaseConfig()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Tracker.getDatabaseConfig', $args);
        return $args;
    }

    function TrackerGetJavascriptCode()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Tracker.getJavascriptCode', $args);
        return $args;
    }

    function TrackerIsExcludedVisit()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Tracker.isExcludedVisit', $args);
        return $args;
    }

    function TrackerMakeNewVisitObject()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Tracker.makeNewVisitObject', $args);
        return $args;
    }

    function TrackerPageUrlGetQueryParametersToExclude()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Tracker.PageUrl.getQueryParametersToExclude', $args);
        return $args;
    }

    function TrackerRequestGetIdSite()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Tracker.Request.getIdSite', $args);
        return $args;
    }

    function TrackerSetTrackerCacheGeneral()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Tracker.setTrackerCacheGeneral', $args);
        return $args;
    }

    function TrackingSpamPreventionBanIp()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TrackingSpamPrevention.banIp', $args);
        return $args;
    }

    function TranslateGetClientSideTranslationKeys()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Translate.getClientSideTranslationKeys', $args);
        return $args;
    }

    function TwoFactorAuthDisabled()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TwoFactorAuth.disabled', $args);
        return $args;
    }

    function TwoFactorAuthEnabled()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TwoFactorAuth.enabled', $args);
        return $args;
    }

    function TwoFactorAuthRequiresTwoFactorAuthentication()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('TwoFactorAuth.requiresTwoFactorAuthentication', $args);
        return $args;
    }

    function UpdaterComponentInstalled()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Updater.componentInstalled', $args);
        return $args;
    }

    function UpdaterComponentUninstalled()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Updater.componentUninstalled', $args);
        return $args;
    }

    function UpdaterComponentUpdated()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Updater.componentUpdated', $args);
        return $args;
    }

    function UserIsNotAuthorized()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('User.isNotAuthorized', $args);
        return $args;
    }

    function UserSettingsUpdated()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('UserSettings.updated', $args);
        return $args;
    }

    function UsersManagerAddUserEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('UsersManager.addUser.end', $args);
        return $args;
    }

    function UsersManagerCheckPassword()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('UsersManager.checkPassword', $args);
        return $args;
    }

    function UsersManagerDeleteUser()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('UsersManager.deleteUser', $args);
        return $args;
    }

    function UsersManagerGetDefaultDates()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('UsersManager.getDefaultDates', $args);
        return $args;
    }

    function UsersManagerInviteUserAccepted()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('UsersManager.inviteUser.accepted', $args);
        return $args;
    }

    function UsersManagerInviteUserDeclined()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('UsersManager.inviteUser.declined', $args);
        return $args;
    }

    function UsersManagerInviteUserEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('UsersManager.inviteUser.end', $args);
        return $args;
    }

    function UsersManagerInviteUserGenerateInviteLinkToken()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('UsersManager.inviteUser.generateInviteLinkToken', $args);
        return $args;
    }

    function UsersManagerInviteUserResendInvite()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('UsersManager.inviteUser.resendInvite', $args);
        return $args;
    }

    function UsersManagerRemoveSiteAccess()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('UsersManager.removeSiteAccess', $args);
        return $args;
    }

    function UsersManagerUpdateUserEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('UsersManager.updateUser.end', $args);
        return $args;
    }

    function ViewDataTableConfigure()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ViewDataTable.configure', $args);
        return $args;
    }

    function ViewDataTableConfigureEnd()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ViewDataTable.configure.end', $args);
        return $args;
    }

    function ViewDataTableFilterViewDataTable()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('ViewDataTable.filterViewDataTable', $args);
        return $args;
    }

    function VisualizationBeforeRender()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Visualization.beforeRender', $args);
        return $args;
    }

    function WidgetAddWidgetConfigs()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Widget.addWidgetConfigs', $args);
        return $args;
    }

    function WidgetFilterWidgets()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Widget.filterWidgets', $args);
        return $args;
    }

    function WidgetizeShouldEmbedIframeEmpty()
    {
        $args = func_get_args();
        $this->displayHooksViewerItem('Widgetize.shouldEmbedIframeEmpty', $args);
        return $args;
    }


}
