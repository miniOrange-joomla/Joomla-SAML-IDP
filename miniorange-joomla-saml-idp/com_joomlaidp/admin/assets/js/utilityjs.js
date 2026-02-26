function add_css_tab(element) {
    jQuery(".mo_nav_tab_active ").removeClass("mo_nav_tab_active").removeClass("active");
    jQuery(element).addClass("mo_nav_tab_active");
}

function is_valid_URL(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}

jQuery(document).ready(function () {
    jQuery(".mo_idp_users_details").change(function () {
        
        jQuery(this).parent().parent().parent().parent().siblings().children().children().children().children(this).val(jQuery(this).val());

        jQuery("#mo_idp_price_slab1_100").css("display", "none");
        jQuery("#mo_idp_price_slab1_200").css("display", "none");
        jQuery("#mo_idp_price_slab1_300").css("display", "none");
        jQuery("#mo_idp_price_slab1_400").css("display", "none");
        jQuery("#mo_idp_price_slab1_500").css("display", "none");
        jQuery("#mo_idp_price_slab1_750").css("display", "none");
        jQuery("#mo_idp_price_slab1_1000").css("display", "none");
        jQuery("#mo_idp_price_slab1_2000").css("display", "none");
        jQuery("#mo_idp_price_slab1_3000").css("display", "none");
        jQuery("#mo_idp_price_slab1_4000").css("display", "none");
        jQuery("#mo_idp_price_slab1_5000").css("display", "none");
        jQuery("#mo_idp_price_slab1_5000p").css("display", "none");
        if(jQuery(this).val()=='5000p')
        {
            jQuery(".mo_idp_plans").css("display", "none");
        }
        else
        {
            jQuery(".mo_idp_plans").css("display", "block");
        }
        jQuery("#mo_idp_price_slab1_"+jQuery(this).val()).css("display", "block");

       
    });

    jQuery('#upload_metadata_file').click(function(){
        var file = document.getElementById("metadata_uploaded_file");
        var sp_name = jQuery("#sp_upload_name").val();
        if(file.files.length != 0 && sp_name!==''){
            jQuery('#IDP_metadata_form').submit();
        } 
        else if(sp_name=='')
        {
            alert("Please Enter the SP name");   
        }else {
            alert("Please upload the metadata file");
            jQuery('#metadata_url').attr('required',false);
            jQuery('#metadata_uploaded_file').attr('required',true);
        }
            
    });

    jQuery('#fetch_metadata').click(function(){
        var url = jQuery("#metadata_url").val();
        var sp_name = jQuery("#sp_upload_name").val();
        if(sp_name=='')
        {
            alert("Please Enter the SP name");   
        }
        else if(!is_valid_URL(url))
        {
            alert("Please Enter the URL.");  
        }
        else if(url!='' && sp_name!=='' )
        {
            jQuery('#IDP_metadata_form').submit(); 
        }
        else{
            alert("Please enter the metadata URL");
            jQuery('#metadata_uploaded_file').attr('required',false);
            jQuery('#metadata_url').attr('required',true);
        }
                
        });

});

function copyToClipboard(element) {
    jQuery(".selected-text").removeClass("selected-text");
    var temp = jQuery("<input>");
    jQuery("body").append(temp);
    jQuery(element).addClass("selected-text");
    temp.val(jQuery(element).text().trim()).select();
    document.execCommand("copy");
    temp.remove();
    jQuery(element).parent().siblings().children().children('.copied_text').text('Copied');
    jQuery(element).siblings().children('.copied_text').text('Copied');
}

jQuery(window).click(function (e) {
    if (e.target.className === undefined || e.target.className.indexOf("fa-copy") === -1)
        jQuery(".selected-text").removeClass("selected-text");
});
function showmodal(){
    jQuery('#myModal').css("display","block");
}
function hidemodal(){
    jQuery('#myModal').css("display","none");
}
function show_gen_cert_form() {
    jQuery("#generate_certificate_form").show();
    jQuery("#mo_gen_cert").hide();
    jQuery("#mo_gen_tab").hide();
}
function hide_gen_cert_form() {
    jQuery("#generate_certificate_form").hide();
    jQuery("#mo_gen_cert").show();
    jQuery("#mo_gen_tab").show();
}
window.addEventListener('DOMContentLoaded', function () {
        let supportButtons = document.getElementsByClassName('mo_saml_idp_request_quote');
        let supportForms = document.getElementsByClassName('mo_saml_idp_request_quote_form');
        for (let i = 0; i < supportButtons.length; i++) {
            supportButtons[i].addEventListener("click", function (e) {
                if (supportForms[0].style.right !== "0px") {
                    supportForms[0].style.right = "0px";
                } else {
                    supportForms[0].style.right = "-391px";
                }
            });
        }
    }
);

function guide(gvalue) {
    if (gvalue !== '1')
        window.open(gvalue);
}

function showTestWindow() {
    var issuer = jQuery('#sp_entityid').val();
    var acsUrl = jQuery('#acs_url').val();
    if (issuer === "" || acsUrl === "") {
        alert("Please provide your SP details first and then click on Test Configuration button.")
    } else {
        var url = jQuery('#idp-initiated-url').val();
        var idpInitiatedUrl = url + '&acs=' + acsUrl + '&issuer=' + issuer;
        var myWindow = window.open(idpInitiatedUrl, 'TEST SAML IDP', 'scrollbars=1 width=800, height=600');
    }
}

function show_metadata_form() {
    jQuery(".mo_saml_current_tab").removeClass("mo_saml_current_tab");
    jQuery("#auto_configuration").addClass("mo_saml_current_tab");
    jQuery('#upload_metadata_form').show();
    jQuery('#idpdata').hide();
    jQuery('#tabhead').hide();
}

function hide_metadata_form() {
    jQuery(".mo_saml_current_tab").removeClass("mo_saml_current_tab");
    jQuery("#manual_configuration").addClass("mo_saml_current_tab");
    jQuery('#upload_metadata_form').hide();
    jQuery('#idpdata').show();
    jQuery('#tabhead').show();
}

function moSAMLAccount() {
    jQuery('a[href="#description"]').click();
    add_css_tab("#accounttab");
}



function MyClose(){
    jQuery("#my_TC_Modal").css("display","none");
    location.reload();
}
function show_TC_modal(){
    jQuery("#my_TC_Modal").css("display","block");
}


function openTab(evt, vtabName) {
    var i, vtab_content, vtab_btn;
    vtab_content = document.getElementsByClassName("vtab_content");
    for (i = 0; i < vtab_content.length; i++) {
        vtab_content[i].style.display = "none";
    }
    vtab_btn = document.getElementsByClassName("vtab_btn");
    for (i = 0; i < vtab_btn.length; i++) {
        vtab_btn[i].className = vtab_btn[i].className.replace(" active", "");
    }
    document.getElementById(vtabName).style.display = "block";
    evt.currentTarget.className += " active";
}

document.addEventListener('DOMContentLoaded', function () {
    var faqHeaders = document.querySelectorAll('.mo_saml_faq_page');
    faqHeaders.forEach(function(header) {
        header.addEventListener('click', function() {
            var body = this.nextElementSibling;
            body.style.display = body.style.display === 'none' || body.style.display =="" ? 'block' : 'none';
        });
    });

    // Support form: capture browser timezone into hidden fields if present
    (function setClientTimezoneFields() {
        var tzEl = document.getElementById('moClientTimezone');
        var offsetEl = document.getElementById('moClientTimezoneOffset');
        if (!tzEl && !offsetEl) {
            return;
        }
        var tzName = '';
        try {
            tzName = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        } catch (e) {
            tzName = '';
        }
        var offsetMinutes = new Date().getTimezoneOffset();
        if (tzEl) tzEl.value = tzName;
        if (offsetEl) offsetEl.value = String(offsetMinutes);
    })();

    // Support form: populate country code dropdown (requires country.js)
    if (typeof moIdpPopulateCountryCodeSelect === 'function') {
        moIdpPopulateCountryCodeSelect('mo_saml_country_code', '91');
    }
});


// Function to show metadata tabs
function showMetadataTab(tabName, event) {
    // Prevent default link behavior (page scroll to top)
    if (event) {
        event.preventDefault();
    }

    // Hide all metadata tab contents
    var tabContents = document.querySelectorAll('.metadata-tab-content');
    tabContents.forEach(function (content) {
        content.classList.remove('mo_boot_display_block');
        content.classList.add('mo_saml_display_none');
    });

    // Remove active class from all metadata tab buttons only
    var tabButtons = document.querySelectorAll('#metadata-url-tab-btn, #download-xml-tab-btn, #manual-info-tab-btn');
    tabButtons.forEach(function (button) {
        button.classList.remove('mo_idp_tab_current');
    });

    // Add active class to selected metadata tab button
    var selectedButton = document.getElementById(tabName + '-tab-btn');
    if (selectedButton) {
        selectedButton.classList.add('mo_idp_tab_current');
    }

    // Show the selected tab content
    var selectedTab = document.getElementById(tabName + '-tab');
    if (selectedTab) {
        selectedTab.classList.remove('mo_saml_display_none');
        selectedTab.classList.add('mo_boot_display_block');
    }
}


// Function to toggle additional attributes section
function toggleAdditionalAttributes() {
    const content = document.getElementById('additional-attributes-content');
    const toggle = document.getElementById('additional-attributes-toggle');

    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        toggle.innerHTML = '-';
    } else {
        content.style.display = 'none';
        toggle.innerHTML = '+';
    }
}

// Function to toggle included features section
function toggleIncludedFeatures(featureId, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    const featureList = document.getElementById(featureId);
    if (featureList) {
        // Toggle using class instead of inline style for better control
        if (featureList.classList.contains('mo_features_expanded')) {
            featureList.classList.remove('mo_features_expanded');
            featureList.style.display = 'none';
        } else {
            featureList.style.display = 'block';
            featureList.classList.add('mo_features_expanded');
        }
    }
    return false;
}

function toggleUpgradeSection() {
    var upgradeContent = document.getElementById('upgrade-content');
    var toggleButton = document.getElementById('upgrade-toggle');

    if (upgradeContent.style.display === 'none' || upgradeContent.style.display === '') {
        upgradeContent.style.display = 'block';
        toggleButton.innerHTML = '-';
    } else {
        upgradeContent.style.display = 'none';
        toggleButton.innerHTML = '+';
    }
}

// Function to toggle licensing section
function toggleLicensingSection() {
    var licensingContent = document.getElementById('licensing-content');
    var toggleButton = document.getElementById('licensing-toggle');

    if (licensingContent.style.display === 'none' || licensingContent.style.display === '') {
        licensingContent.style.display = 'block';
        toggleButton.innerHTML = '-';
    } else {
        licensingContent.style.display = 'none';
        toggleButton.innerHTML = '+';
    }
}

// Function to toggle advanced features section
function toggleAdvancedFeatures() {
    var advancedContent = document.getElementById('advanced-features-content');
    var toggleButton = document.getElementById('advanced-features-toggle');

    if (advancedContent.style.display === 'none' || advancedContent.style.display === '') {
        advancedContent.style.display = 'block';
        toggleButton.innerHTML = '-';
    } else {
        advancedContent.style.display = 'none';
        toggleButton.innerHTML = '+';
    }
}


function showImportExportConfig() {
    var listTable = document.getElementById('sp_list_table');
    var importExportDiv = document.getElementById('mo_idp_import_export_id');
    
    if (listTable) {
        listTable.style.display = 'none';
    }
    
    if (importExportDiv) {
        importExportDiv.classList.remove('mo_saml_display_none');
        importExportDiv.style.setProperty('display', 'block', 'important');
    } else {
        console.error('Import/Export div not found!');
    }
}

function backToIdpList() {
    var listTable = document.getElementById('sp_list_table');
    var importExportDiv = document.getElementById('mo_idp_import_export_id');
    
    if (importExportDiv) {
        importExportDiv.style.display = 'none';
        importExportDiv.classList.add('mo_saml_display_none');
    }
    
    if (listTable) {
        listTable.style.display = 'block';
    }
}