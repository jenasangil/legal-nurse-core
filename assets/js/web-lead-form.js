/* Legal Nurse Core — Web Lead Form tracking + Creatio integration.
 * Config (Form ID, Thank-You URL, Creatio service/landing) is provided by the
 * widget via window.LNC_LEAD_FORM_CONFIG. The two Creatio libraries
 * (track-cookies.js, create-object.js) are enqueued as dependencies. */
(function () {
    "use strict";

    /* =========================================================
       CONFIGURATION (from the widget)
    ========================================================= */

    var CFG = window.LNC_LEAD_FORM_CONFIG || {};

    const FORM_ID = parseInt(CFG.formId, 10) || 1;

    const THANK_YOU_URL = CFG.thankYouUrl || "/thank-you";

    const CREATIO_SERVICE_URL =
        CFG.creatioServiceUrl ||
        "https://legalnurse.creatio.com/0/" +
        "ServiceModel/" +
        "GeneratedObjectWebFormService.svc/" +
        "SaveWebFormObjectData";

    const CREATIO_LANDING_ID =
        CFG.creatioLandingId ||
        "5eb21031-3a9b-47ac-979b-5e3f1b61e846";

    const TRACKING_STORAGE_KEY = "legalnurse_tracking_data";
    const SESSION_ID_KEY = "legalnurse_tracking_session_id";
    const SESSION_ACTIVITY_KEY = "legalnurse_tracking_last_activity";
    const SUBMISSION_CACHE_KEY = "legalnurse_pending_creatio_submission";
    const SESSION_TIMEOUT = 30 * 60 * 1000;
    const CREATIO_TIMEOUT = 20000;

    let pendingCreatioSubmission = null;
    let creatioStarted = false;
    let creatioCompleted = false;
    let creatioTimeoutTimer = null;

    /* =========================================================
       GRAVITY FORMS FIELD MAP
    ========================================================= */

    const trackingFields = {
        leadSource: 14, gclid: 15, msclkid: 16, gbraid: 17, wbraid: 18,
        matchtype: 19, keyword: 20, gkeyword: 21,
        utm_source: 22, utm_medium: 23, utm_id: 24, utm_content: 25,
        utm_campaign: 26, utm_term: 27,
        fbclid: 28, fb_ad_id: 29, ad_id: 30, fb_adset_id: 31, adset_id: 32,
        fb_campaign_id: 33, campaign_id: 34, placement: 35,
        li_fat_id: 36, li_source: 37,
        network: 38, device: 39, adgroupid: 40, creative: 41, adposition: 42,
        landing_page: 43, landing_page_no_params: 44, referrer: 45,
        timestamp: 46, session_id: 47, device_type: 48, user_agent: 49,
        form_id: 50, conversion_point: 51, geo: 52, click_path: 54
    };

    const submissionFields = {
        firstName: 1, lastName: 3, email: 5, phone: 6, license: 7,
        address: 9, city: 10, state: "13_4", zip: 12,
        leadSource: 14, gclid: 15, msclkid: 16, gbraid: 17, wbraid: 18,
        matchtype: 19, keyword: 20, gkeyword: 21,
        utm_source: 22, utm_medium: 23, utm_id: 24, utm_content: 25,
        utm_campaign: 26, utm_term: 27,
        fbclid: 28, fb_ad_id: 29, ad_id: 30, fb_adset_id: 31, adset_id: 32,
        fb_campaign_id: 33, campaign_id: 34, placement: 35,
        li_fat_id: 36, li_source: 37,
        network: 38, device: 39, adgroupid: 40, creative: 41, adposition: 42,
        landing_page: 43, landing_page_no_params: 44, referrer: 45,
        timestamp: 46, session_id: 47, device_type: 48, user_agent: 49,
        form_id: 50, conversion_point: 51, geo: 52, click_path: 54
    };

    /* =========================================================
       TRACKING STORAGE
    ========================================================= */

    function getUrlParameters() {
        const result = {};
        const parameters = new URLSearchParams(window.location.search);
        parameters.forEach(function (value, key) { result[key] = value; });
        return result;
    }

    function getStoredTrackingData() {
        try {
            const stored = localStorage.getItem(TRACKING_STORAGE_KEY);
            return stored ? JSON.parse(stored) : {};
        } catch (error) {
            console.error("[Tracking] Could not read tracking data.", error);
            return {};
        }
    }

    function saveTrackingData(data) {
        try {
            localStorage.setItem(TRACKING_STORAGE_KEY, JSON.stringify(data));
        } catch (error) {
            console.error("[Tracking] Could not save tracking data.", error);
        }
    }

    function urlOrStored(parameters, stored, name) {
        return parameters[name] || stored[name] || "";
    }

    /* =========================================================
       SESSION AND DEVICE
    ========================================================= */

    function getSessionId() {
        const now = Date.now();
        let sessionId = localStorage.getItem(SESSION_ID_KEY);
        const lastActivity = parseInt(localStorage.getItem(SESSION_ACTIVITY_KEY), 10);

        if (!sessionId || !lastActivity || now - lastActivity > SESSION_TIMEOUT) {
            sessionId = "sess_" + now + "_" + Math.random().toString(36).substring(2, 11);
            localStorage.setItem(SESSION_ID_KEY, sessionId);
        }
        localStorage.setItem(SESSION_ACTIVITY_KEY, String(now));
        return sessionId;
    }

    function getDeviceType() {
        const userAgent = navigator.userAgent.toLowerCase();
        if (/tablet|ipad|playbook|silk/i.test(userAgent)) { return "tablet"; }
        if (/mobile|iphone|ipod|android|blackberry|opera mini|iemobile|palm|smartphone/i.test(userAgent)) { return "mobile"; }
        return "desktop";
    }

    /* =========================================================
       CAPTURE TRACKING
    ========================================================= */

    function captureTrackingData() {
        const params = getUrlParameters();
        const stored = getStoredTrackingData();
        const currentUrl = window.location.href;
        const currentUrlWithoutParameters = window.location.origin + window.location.pathname;
        const timestamp = new Date().toISOString();

        const data = {
            leadSource: params.lead || stored.leadSource || "",
            gclid: urlOrStored(params, stored, "gclid"),
            msclkid: urlOrStored(params, stored, "msclkid"),
            gbraid: urlOrStored(params, stored, "gbraid"),
            wbraid: urlOrStored(params, stored, "wbraid"),
            matchtype: urlOrStored(params, stored, "matchtype"),
            keyword: urlOrStored(params, stored, "keyword"),
            gkeyword: params.gkeyword || params.keyword || stored.gkeyword || stored.keyword || "",
            utm_source: urlOrStored(params, stored, "utm_source"),
            utm_medium: urlOrStored(params, stored, "utm_medium"),
            utm_id: urlOrStored(params, stored, "utm_id"),
            utm_content: urlOrStored(params, stored, "utm_content"),
            utm_campaign: urlOrStored(params, stored, "utm_campaign"),
            utm_term: urlOrStored(params, stored, "utm_term"),
            fbclid: urlOrStored(params, stored, "fbclid"),
            fb_ad_id: params.fb_ad_id || params.ad_id || stored.fb_ad_id || "",
            ad_id: params.ad_id || params.fb_ad_id || stored.ad_id || "",
            fb_adset_id: params.fb_adset_id || params.adset_id || stored.fb_adset_id || "",
            adset_id: params.adset_id || params.fb_adset_id || stored.adset_id || "",
            fb_campaign_id: params.fb_campaign_id || params.campaign_id || stored.fb_campaign_id || "",
            campaign_id: params.campaign_id || params.fb_campaign_id || stored.campaign_id || "",
            placement: urlOrStored(params, stored, "placement"),
            li_fat_id: urlOrStored(params, stored, "li_fat_id"),
            li_source: urlOrStored(params, stored, "li_source"),
            network: urlOrStored(params, stored, "network"),
            device: urlOrStored(params, stored, "device"),
            adgroupid: urlOrStored(params, stored, "adgroupid"),
            creative: urlOrStored(params, stored, "creative"),
            adposition: urlOrStored(params, stored, "adposition"),
            landing_page: stored.landing_page || currentUrl,
            landing_page_no_params: stored.landing_page_no_params || currentUrlWithoutParameters,
            referrer: stored.referrer || document.referrer || "",
            timestamp: timestamp,
            session_id: getSessionId(),
            device_type: params.device_type || stored.device_type || getDeviceType(),
            user_agent: params.user_agent || stored.user_agent || navigator.userAgent,
            form_id: "gravity_form_" + FORM_ID,
            conversion_point: "form_submit",
            geo: stored.geo || "",
            click_path: Array.isArray(stored.click_path) ? stored.click_path : []
        };

        const lastPage = data.click_path.length ? data.click_path[data.click_path.length - 1] : null;
        if (!lastPage || lastPage.url !== currentUrl) {
            data.click_path.push({ url: currentUrl, timestamp: timestamp });
        }

        saveTrackingData(data);
        return data;
    }

    /* =========================================================
       GRAVITY FORMS FIELD HELPERS
    ========================================================= */

    function getGravityField(fieldId) {
        return document.getElementById("input_" + FORM_ID + "_" + fieldId);
    }

    function getGravityFieldValue(fieldId) {
        const field = getGravityField(fieldId);
        return field ? field.value : "";
    }

    function setGravityFieldValue(fieldId, value) {
        const field = getGravityField(fieldId);
        if (!field) { return; }
        field.value = value === undefined || value === null ? "" :
            typeof value === "object" ? JSON.stringify(value) : String(value);
    }

    function populateTrackingFields() {
        const data = getStoredTrackingData();
        data.form_id = "gravity_form_" + FORM_ID;
        data.conversion_point = "form_submit";
        data.timestamp = new Date().toISOString();
        saveTrackingData(data);

        Object.keys(trackingFields).forEach(function (name) {
            setGravityFieldValue(trackingFields[name], data[name] || "");
        });
    }

    /* =========================================================
       GEOLOCATION
    ========================================================= */

    function captureGeoLocation() {
        const stored = getStoredTrackingData();
        if (stored.geo) { setGravityFieldValue(52, stored.geo); return; }

        fetch("https://ipapi.co/json/")
            .then(function (response) {
                if (!response.ok) { throw new Error("Geo lookup failed."); }
                return response.json();
            })
            .then(function (geoData) {
                const geo = [geoData.city || "", geoData.region || "", geoData.country_name || ""].filter(Boolean).join(", ");
                const data = getStoredTrackingData();
                data.geo = geo;
                saveTrackingData(data);
                setGravityFieldValue(52, geo);
            })
            .catch(function (error) {
                console.warn("[Tracking] Geo lookup unavailable.", error);
            });
    }

    /* =========================================================
       CACHE GRAVITY FORMS SUBMISSION
    ========================================================= */

    function cacheSubmissionData() {
        captureTrackingData();
        populateTrackingFields();

        const data = {};
        Object.keys(submissionFields).forEach(function (name) {
            data[name] = getGravityFieldValue(submissionFields[name]);
        });

        data.leadType = "Prospect";
        data.form_id = data.form_id || ("gravity_form_" + FORM_ID);
        data.conversion_point = "form_submit";
        data.timestamp = new Date().toISOString();

        pendingCreatioSubmission = data;
        try {
            sessionStorage.setItem(SUBMISSION_CACHE_KEY, JSON.stringify(data));
        } catch (error) {
            console.error("[Creatio] Could not cache submission.", error);
        }

        console.log("[Creatio] Submission data cached:", data);
        return data;
    }

    function getCachedSubmissionData() {
        if (pendingCreatioSubmission) { return pendingCreatioSubmission; }
        try {
            const cached = sessionStorage.getItem(SUBMISSION_CACHE_KEY);
            if (cached) {
                pendingCreatioSubmission = JSON.parse(cached);
                return pendingCreatioSubmission;
            }
        } catch (error) {
            console.error("[Creatio] Could not read cached submission.", error);
        }
        return null;
    }

    function prepareGravitySubmission() {
        cacheSubmissionData();
        console.log("[Creatio] Gravity Forms submission prepared.");
    }

    /* =========================================================
       CREATIO PROXY FIELDS
    ========================================================= */

    function createCreatioProxyFields(data) {
        const existing = document.getElementById("creatio-proxy-fields");
        if (existing) { existing.remove(); }

        const container = document.createElement("div");
        container.id = "creatio-proxy-fields";
        container.style.display = "none";
        container.setAttribute("aria-hidden", "true");

        Object.keys(data).forEach(function (name) {
            const input = document.createElement("input");
            input.type = "hidden";
            input.id = "creatio_" + name;
            input.name = "creatio_" + name;
            input.value = data[name] === undefined || data[name] === null ? "" : String(data[name]);
            container.appendChild(input);
        });

        document.body.appendChild(container);
    }

    function restoreCreatioProxyFields(data) {
        Object.keys(data).forEach(function (name) {
            const field = document.getElementById("creatio_" + name);
            if (field) {
                field.value = data[name] === undefined || data[name] === null ? "" : String(data[name]);
            }
        });
    }

    /* =========================================================
       CREATIO CONFIGURATION
    ========================================================= */

    function getCreatioConfig() {
        return {
            fields: {
                LN1LeadNurseType: "#creatio_license",
                LeadType: "#creatio_leadType",
                LN1LegalNurseLeadSource: "#creatio_leadSource",
                LN1LeadLastName: "#creatio_lastName",
                LN1LeadFirstName: "#creatio_firstName",
                Email: "#creatio_email",
                MobilePhone: "#creatio_phone",
                Address: "#creatio_address",
                CityStr: "#creatio_city",
                RegionStr: "#creatio_state",
                Zip: "#creatio_zip",
                LN1gclid: "#creatio_gclid",
                LN1msclkid: "#creatio_msclkid",
                LN1gbraid: "#creatio_gbraid",
                LN1wbraid: "#creatio_wbraid",
                LN1matchtype: "#creatio_matchtype",
                LN1keyword: "#creatio_keyword",
                LN1GKeyword: "#creatio_gkeyword",
                LN1utm_source: "#creatio_utm_source",
                LN1utm_medium: "#creatio_utm_medium",
                LN1utm_id: "#creatio_utm_id",
                LN1utm_content: "#creatio_utm_content",
                LN1utm_campaign: "#creatio_utm_campaign",
                LN1utm_term: "#creatio_utm_term",
                LN1fbclid: "#creatio_fbclid",
                LN1fb_ad_id: "#creatio_fb_ad_id",
                LN1ad_id: "#creatio_ad_id",
                LN1fb_adset_id: "#creatio_fb_adset_id",
                LN1adset_id: "#creatio_adset_id",
                LN1fb_campaign_id: "#creatio_fb_campaign_id",
                LN1campaign_id: "#creatio_campaign_id",
                LN1placement: "#creatio_placement",
                LN1li_fat_id: "#creatio_li_fat_id",
                LN1li_source: "#creatio_li_source",
                LN1network: "#creatio_network",
                LN1device: "#creatio_device",
                LN1adgroupid: "#creatio_adgroupid",
                LN1creative: "#creatio_creative",
                LN1adposition: "#creatio_adposition",
                LN1landing_page: "#creatio_landing_page",
                LN1landing_page_no_params: "#creatio_landing_page_no_params",
                LN1referrer: "#creatio_referrer",
                LN1timestamp: "#creatio_timestamp",
                LN1session_id: "#creatio_session_id",
                LN1device_type: "#creatio_device_type",
                LN1user_agent: "#creatio_user_agent",
                LN1form_id: "#creatio_form_id",
                LN1conversion_point: "#creatio_conversion_point",
                LN1geo: "#creatio_geo",
                LN1click_path: "#creatio_click_path"
            },
            customFields: {},
            landingId: CREATIO_LANDING_ID,
            serviceUrl: CREATIO_SERVICE_URL,
            redirectUrl: ""
        };
    }

    /* =========================================================
       STATUS MESSAGE
    ========================================================= */

    function getStatusContainer() {
        let status = document.getElementById("creatio-submission-status");
        if (status) { return status; }

        status = document.createElement("div");
        status.id = "creatio-submission-status";

        const confirmation = document.getElementById("gform_confirmation_wrapper_" + FORM_ID);
        if (confirmation) { confirmation.appendChild(status); } else { document.body.appendChild(status); }
        return status;
    }

    function updateSubmissionStatus(message, isError) {
        const status = getStatusContainer();
        status.innerHTML = "<p>" + message + "</p>";
        status.style.color = isError ? "#b42318" : "";
    }

    function showRetryMessage(message) {
        const status = getStatusContainer();
        status.innerHTML = "<p>" + message + "</p>" +
            '<button type="button" id="retry-creatio-submission">Try Again</button>';
        status.style.color = "#b42318";

        const retryButton = document.getElementById("retry-creatio-submission");
        if (retryButton) {
            retryButton.addEventListener("click", function () {
                creatioStarted = false;
                creatioCompleted = false;
                submitToCreatio();
            });
        }
    }

    /* =========================================================
       CREATIO RESPONSE
    ========================================================= */

    function isSuccessfulCreatioResponse(xhr) {
        if (xhr.status < 200 || xhr.status >= 300) { return false; }
        if (!xhr.responseText) { return true; }
        try {
            const response = JSON.parse(xhr.responseText);
            if (response.success === false || response.Success === false) { return false; }
            return true;
        } catch (error) {
            return true;
        }
    }

    function completeCreatioSubmission() {
        if (creatioCompleted) { return; }
        creatioCompleted = true;
        if (creatioTimeoutTimer) { clearTimeout(creatioTimeoutTimer); }

        updateSubmissionStatus("Submission completed. Redirecting...", false);
        pendingCreatioSubmission = null;
        try { sessionStorage.removeItem(SUBMISSION_CACHE_KEY); } catch (error) {}

        setTimeout(function () { window.location.assign(THANK_YOU_URL); }, 500);
    }

    /* =========================================================
       MONITOR CREATIO XHR
    ========================================================= */

    const originalXhrOpen = XMLHttpRequest.prototype.open;
    const originalXhrSend = XMLHttpRequest.prototype.send;

    XMLHttpRequest.prototype.open = function (method, url) {
        this._legalNurseRequestUrl = url;
        return originalXhrOpen.apply(this, arguments);
    };

    XMLHttpRequest.prototype.send = function (body) {
        const xhr = this;
        const isCreatioRequest = xhr._legalNurseRequestUrl &&
            xhr._legalNurseRequestUrl.indexOf("SaveWebFormObjectData") !== -1;

        if (isCreatioRequest) {
            console.log("[Creatio] Request started:", xhr._legalNurseRequestUrl);

            xhr.addEventListener("load", function () {
                console.log("[Creatio] Response:", { status: xhr.status, response: xhr.responseText });
                if (isSuccessfulCreatioResponse(xhr)) {
                    completeCreatioSubmission();
                } else {
                    creatioStarted = false;
                    showRetryMessage("Your form was saved, but the final connection could not be completed. Please try again.");
                }
            });

            xhr.addEventListener("error", function () {
                creatioStarted = false;
                console.error("[Creatio] Network error.");
                showRetryMessage("Your form was saved, but a network problem interrupted the final connection. Please try again.");
            });

            xhr.addEventListener("timeout", function () {
                creatioStarted = false;
                showRetryMessage("The final connection took too long. Please try again.");
            });
        }

        return originalXhrSend.apply(this, arguments);
    };

    /* =========================================================
       SEND DATA TO CREATIO
    ========================================================= */

    function submitToCreatio() {
        if (creatioStarted || creatioCompleted) { return; }

        const data = getCachedSubmissionData();
        console.log("[Creatio] Cached submission:", data);

        if (!data) {
            showRetryMessage("Your form was saved, but the final submission data could not be prepared.");
            return;
        }

        if (!window.landing || typeof window.landing.createObjectFromLanding !== "function") {
            showRetryMessage("Your form was saved, but the Creatio connection is unavailable. Please try again.");
            return;
        }

        creatioStarted = true;
        updateSubmissionStatus("Thank you. We are completing your submission...", false);
        createCreatioProxyFields(data);

        const config = getCreatioConfig();

        try {
            if (typeof window.landing.initLanding === "function") {
                window.landing.initLanding(config);
                restoreCreatioProxyFields(data);
            }

            creatioTimeoutTimer = setTimeout(function () {
                if (!creatioCompleted) {
                    creatioStarted = false;
                    showRetryMessage("The final connection took too long. Please try again.");
                }
            }, CREATIO_TIMEOUT);

            window.landing.createObjectFromLanding(config);
        } catch (error) {
            creatioStarted = false;
            if (creatioTimeoutTimer) { clearTimeout(creatioTimeoutTimer); }
            console.error("[Creatio] Submission error:", error);
            showRetryMessage("Your form was saved, but the final connection could not be completed. Please try again.");
        }
    }

    /* =========================================================
       PAGE INITIALIZATION
    ========================================================= */

    function gravityConfirmationIsVisible() {
        return Boolean(
            document.getElementById("gform_confirmation_wrapper_" + FORM_ID) ||
            document.getElementById("gform_confirmation_message_" + FORM_ID) ||
            document.getElementById("creatio-submission-status")
        );
    }

    function initializePage() {
        if (gravityConfirmationIsVisible()) {
            console.log("[Creatio] Gravity Forms confirmation detected.");
            submitToCreatio();
            return;
        }

        captureTrackingData();
        populateTrackingFields();
        captureGeoLocation();
    }

    /* =========================================================
       CACHE BEFORE NORMAL FORM POSTBACK
    ========================================================= */

    document.addEventListener("click", function (event) {
        if (!event.target || typeof event.target.closest !== "function") { return; }
        const submitButton = event.target.closest("#gform_submit_button_" + FORM_ID);
        if (submitButton) { prepareGravitySubmission(); }
    }, true);

    document.addEventListener("submit", function (event) {
        if (event.target && event.target.id === "gform_" + FORM_ID) {
            prepareGravitySubmission();
        }
    }, true);

    /* =========================================================
       START
    ========================================================= */

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initializePage);
    } else {
        initializePage();
    }

    /* =========================================================
       CONSOLE TESTING HELPERS
    ========================================================= */

    window.getLegalNurseTrackingData = getStoredTrackingData;
    window.getLegalNursePendingSubmission = getCachedSubmissionData;
    window.populateGravityTrackingFields = populateTrackingFields;
    window.retryLegalNurseCreatio = function () {
        creatioStarted = false;
        creatioCompleted = false;
        submitToCreatio();
    };
})();
