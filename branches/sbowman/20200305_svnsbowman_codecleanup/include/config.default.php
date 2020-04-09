<?php
/* Default ResourceSpace Configuration Settings  **** WARNING: DO NOT ALTER THIS FILE! ****
 * If you need to change any of the values below, copy them to ../include/config.php and change them there.
 *
 * This file will be overwritten when you upgrade and ensures that any new configuration options are set to a sensible
 *  default value.  Many of these options can also be overridden by user group in System Setup.
 *
 * Developers: Please add new configuration options to the appropriate setting catagories to keep this file well
 *  organized.
 *
 * Configuration Option Setting Catagories:
 *     WEB SERVER SETTINGS
 *         MySQL DATABASE SERVER SETTINGS
 *         PHP SETTINGS
 *         WEB SERVER SETTINGS
 *         CROSS-SITE REQUEST FORGERY (CSRF) AND CROSS-ORIGIN RESOURCE SHARING (CORS) SETTINGS
 *         WEB SERVER SECURITY SETTINGS
 *     BASE RESOURCESPACE SETTINGS
 *         PATHS TO EXTERNAL UTILITIES AND DEPENDENCIES
 *         STORAGE SETTINGS
 *         WEB SPIDER SETTINGS
 *         LOGGING SETTINGS
 *         LANGUAGE SETTINGS
 *         FILE TRANSFER PROTOCOL (FTP) SETTINGS
 *         RESOURCE SHARING SETTINGS
 *         EMAIL SETTINGS
 *         SYSTEM EMAIL SETTINGS
 *         CKEDITOR ABND SITE CONTENT SETTINGS
 *         HELP SETTINGS
 *         PLUGIN SETTINGS
 *         INDEXING AND RELATED SETTINGS
 *         OFFLINE JOB QUEUE SETTINGS
 *         REPORTING SETTINGS
 *         KEYBOARD CONTROL CODE SETTINGS
 *         DISK SPACE QUOTA SETTINGS
 *         SPECIAL ADMINISTRATIVE SETTINGS
 *     USER SETTINGS
 *         USER GROUP SETTINGS
 *         USER MANAGEMENT SETTINGS
 *         USER PASSWORD SETTINGS
 *         ANONYMOUS USER SETTINGS
 *         USER PREFERENCES SETTINGS
 *         USER ACTION SETTINGS
 *         USER AUTO-ACCOUNT SETTINGS
 *         CUSTOM USER SETTINGS
 *     WEBPAGE FORMATTING SETTINGS
 *         GENERAL FORMATTING SETTINGS
 *         PAGE HEADER SETTINGS
 *         PAGE FOOTER SETTINGS
 *         SLIDESHOW SETTINGS
 *         HOMEPAGE SETTINGS
 *         TOP NAVIGATION BAR SETTINGS
 *         GENERAL FORMATTING SETTINGS
 *         HOME DASH AND TILE SETTINGS
 *     SEARCH SETTINGS
 *         SPECIAL SEARCH SETTINGS
 *         SIMPLE SEARCH SETTINGS
 *         ADVANCED SEARCH
 *         SORTING SETTINGS
 *         RESULTS DISPLAY SETTINGS
 *         SEARCH VIEWS SETTINGS
 *     METADATA SETTINGS
 *         METADATA RESOURCE FIELD SETTINGS
 *         METADATA TEMPLATE SETTINGS
 *         EXIFTOOL SETTINGS
 *     GEOLOCATION SETTINGS
 *         OPENLAYERS SETTINGS
 *     IMAGE PROCESSING SETTINGS
 *         GENERAL PREVIEW SETTINGS
 *         COLOR PROFILE SETTINGS
 *         IMAGEMAGICK SETTINGS
 *         GHOSTSCRIPT SETTINGS
 *         IMAGE PREVIEW GENERATION SETTINGS
 *         IMAGE WATERMARK SETTINGS
 *         PDF FILE SETTINGS
 *         ALTERNATIVE IMAGE SIZE AND FORMAT SETTINGS
 *         QUICKLOOK PREVIEW SETTINGS
 *     VIDEO AND AUDIO PROCESSING SETTINGS
 *         FFMPEG SETTINGS
 *         ALTERNATIVE VIDEO SIZES AND FORMATS
 *         HTTP LIVE STREAMING (HLS) SETTINGS
 *         AUDIO FILE SETTINGS
 *     FEATURED COLLECTIONS (THEMES) SETTINGS
 *         THEME CATEGORY SETTINGS
 *         THEMES SIMPLE VIEW SETTINGS
 *         SMART THEMES SETTINGS
 *         CATEGORY TREE SETTINGS
 *         COLLECTION BAR SETTINGS
 *         BROWSE BAR SETTINGS
 *         COLLECTION SETTINGS
 *         PUBLIC COLLECTION SETTINGS
 *         COLLECTION MANAGEMENT SETTINGS
 *         COLLECTION SHARING SETTINGS
 *         SMART COLLECTION SETTINGS
 *         COLLECTION FEEDBACK SETTINGS
 *     UPLOAD SETTINGS
 *         UPLOAD METADATA OPTION SETTINGS
 *         UPLOAD STATUS AND ACCESS SETTINGS
 *         PLUPLOAD SETTINGS
 *         UPLOAD CHECKSUM SETTINGS
 *         BATCH UPLOAD SETTINGS
 *     DOWNLOAD SETTINGS
 *         DOWNLOAD BROWSER SETTINGS
 *         DOWNLOAD PAGE SETTINGS
 *         METADATA DOWNLOAD SETTINGS
 *     RESOURCE SETTINGS
 *         RESOURCE TYPE SETTINGS
 *         RESOURCE VIEW SETTINGS
 *         RESOURCE EDITING SETTINGS
 *         RESOURCE DELETION SETTINGS
 *         RESOURCE ANNOTATION SETTINGS
 *         RESOURCE REQUEST SETTINGS
 *         RESOURCE RATING SETTINGS
 *         RESOURCE COMMENTING SETTINGS
 *         SPEEDTAGGING SETTINGS
 *     CONTACT SHEET SETTINGS
 *     ECOMMERCE SETTINGS
 *     FACIAL RECOGNITION SETTINGS
 *     IIIF SETTINGS
 *     STATICSYNC FILE IMPORT SETTINGS
 *
 *     DEPREDICATED CONFIGURATION OPTIONS
 *
 * @package ResourceSpace
 * @subpackage Configuration
 */
include "version.php";

//----WEB SERVER SETTINGS-----------------------------
// MySQL DATABASE SERVER SETTINGS
$mysql_server = 'localhost';
$mysql_server_port = 3306;
# $mysql_charset = 'utf8';
$mysql_db = 'resourcespace';
$mysql_username = 'root';
$mysql_password = '';
$read_only_db_username = "";
$read_only_db_password = "";

// Path to the MySQL client binaries (mysqldump) with no trailing slash, only needed if you plan to use the export tool.
$mysql_bin_path = '/usr/bin';

// Force MySQL Strict Mode (regardless of existing setting)? This is useful for developers, so that errors that might
//  only occur when Strict Mode is enabled are caught. Strict Mode is enabled by default with some versions of MySQL.
//  The typical error is caused when the empty string ('') is inserted into a numeric column, when NULL should be
//  inserted instead. With Strict Mode turned off, MySQL inserts NULL without complaining. With Strict Mode turned on,
//  a warning/error is generated.
$mysql_force_strict_mode = false;

// If TRUE, do not remove the backslash from database queries and do not do any special processing to them. Unless you
//  need to store '\' in your fields, you can safely keep the default.
$mysql_verbatim_queries = false;

// Record important database transactions (e.g. INSERT, UPDATE, DELETE) in a SQL log file to allow replaying of changes
//  since DB was last backed up. You may schedule cron jobs to delete this SQL log file and perform a mysqldump of the
//  database at the same time. There is no built in database backup, you need to take care of this yourself.
// WARNING!! Ensure the location defined by $mysql_log_location is not in a web accessible directory. It is advisable
//  to either block access in the web server configuration or make the file write only by the web service account.
$mysql_log_transactions = false;
# $mysql_log_location = '/var/resourcespace_backups/sql_log.sql';

// Use prepared statements? Default is FALSE, until technology is proven.
$use_mysqli_prepared = false;

// Enable establishing secure MySQL connections using SSL? Requires setting up $mysqli_ssl_server_cert and $mysqli_ssl_ca_cert.
$use_mysqli_ssl = false;
# $mysqli_ssl_server_cert = '/etc/ssl/certs/server.pem';
# $mysqli_ssl_ca_cert = '/etc/ssl/certs/ca_chain.pem';


// PHP SETTINGS
// Path to PHP to run certain actions asynchronous, such as video preview transcoding.
# $php_path="/usr/bin";

// PHP execution time limit, default is 5 minutes.
$php_time_limit = 300;

// On some PHP installations, the imagerotate() function is wrong and images are rotated in the opposite direction to
//  that specified in the dropdown on the Edit page.  If so, set to TRUE to rectify.
$image_rotate_reverse_options = false;

// Enable execution lockout mode to prevent entry to PHP even to admin users (e.g. config overrides and upload of new
// plugins)?  Useful on shared or multi-tennant systems.
$execution_lockout = false;

// Enable PHPMailer?
$use_phpmailer = false;


// WEB SERVER SETTINGS
// Base web address for this installation with no trailing slash.
$baseurl = "http://my.site/resourcespace";

// Server charset, needed when dealing with filenames in some situations, such as at collection download.
# $server_charset = ''; # Options: 'UTF-8', 'ISO-8859-1', or 'Windows-1252'

// Local time zone, default is 'GMT'. See https://www.php.net/manual/en/timezones.php for timezone codes.
if(function_exists("date_default_timezone_set"))
    {
    date_default_timezone_set("UTC");
    }

// Configuration used to be allow for date offset based on user local time zone. For this to work, the server (or
//  whatever MySQL uses) should be on the same timezone as PHP.
$user_local_timezone = 'UTC';

// Cron jobs maximum execution time in seconds, default: 30 minutes.
$cron_job_time_limit = 1800;

// Enable hiding error messages?
$show_error_messages = true;


// CROSS-SITE REQUEST FORGERY (CSRF) AND CROSS-ORIGIN RESOURCE SHARING (CORS) SETTINGS
// Enable Cross-Site Request Forgery (CRSF) page protection?
$CSRF_enabled = true;

// CSRF page token identifier.
$CSRF_token_identifier = "CSRFToken";

// Array of pages to exempt from CSRF.
$CSRF_exempt_pages = array("login");

// Array to allow other systems to make cross-origin requests. The elements of this configuration option should follow
//  the "<scheme>://<hostname>" syntax.
$CORS_whitelist = array();


// WEB SERVER SECURITY SETTINGS
// Set to scramble resource paths. If this is a public installation, then this is a very wise idea. Set the scramble
//  key to be a hard-to-guess string (similar to a password). To disable, set to the empty string ("").
$scramble_key = "abcdef123";                            # <-------- Suggest making default much longer.

// If changing the scramble key, set to TRUE. If switching from a non-null keyset, set $scramble_key_old. Run
//  ../pages/tools/xfer_scrambled.php to move the files, but any omitted should be detected by get_resource_path() if
//  this is set.
$migrating_scrambled = false;
# $scramble_key_old = "";

// Enable remote APIs (if using API, RSS2, or other plugins that allow remote authentication via an API key)?
$enable_remote_apis = true;

// If $enable_remote_apis=true, the API scramble key.
$api_scramble_key = "abcdef123";                        # <-------- Suggest making default much longer.

// Array of workflow states to ignore when verifying file integrity (to verify file integrity using checksums requires
//  $file_checksums_50k=false;)
$file_integrity_ignore_states = array();

// Array to set server time window that the file integrity check script can run in. Can be resource intensive when
//  checking checksums for a large number of resources. Examples:
//  $file_integrity_verify_window = array(22, 6); Run between 10PM and 6AM (first hour is later than second, so time
//   must be after first OR before second).
//  $file_integrity_verify_window = array(18, 0); Run between 6PM and 12AM (midnight).
$file_integrity_verify_window = array(0, 0); # Off by default.

// Enable proxy "X-Forwarded-For" Apache header for the IP address. Do not enable if you are not using a proxy, as it
//  will mean IP addresses can be easily faked.
$ip_forwarded_for = false;


//----BASE RESOURCESPACE SETTINGS---------------------------------------------------------------------------------------
// Name of your ResourceSpace application, such as 'MyCompany Resource System'.
$applicationname = "ResourceSpace";

// Application subtitle (i18n translated) if $header_text_title=true;
$applicationdesc = "";

// Enable work-arounds required when installed on Microsoft Windows systems?
$config_windows = false;

// Send occasional statistics to Montala? If TRUE, the number of resources and the number of users metrics will be
//  sent every 7 days. The information will only be used to provide totals on the Montala website, such as the global
//  number of installations, users, and resources.
$send_statistics = true;

// Display an alert icon next to the Admin/System link and the relevant items when there are requests that need managing
//  for users with permissions to do this?
$team_centre_alert_icon = true;

// For offline process locking, e.g. staticsync and create_previews.php, length of time of a lock before it is ignored?
$process_locks_max_seconds = 14400; # 4 hours default (60*60*4).

// Enable display of a system down message to all users?
$system_down_redirect = false;

// Empty the configured temp folder of old files when creating new temporary files, as the number of days. If the age
//  of the temporary folder exceeds this number, then it will be deleted, set to 0 (off) by default. Use with care
//  e.g. make sure your IIS/Apache service account does not have write access to the whole server.
$purge_temp_folder_age = 0;

// X-Frame-Options, options: 'DENY' (prevent all), 'SAMEORIGIN', or 'ALLOW-FROM' with a URL to allow site to be used
//  in an iframe. To disable completely, set to "";
$xframe_options = "SAMEORIGIN";


// PATHS TO EXTERNAL UTILITIES AND DEPENDENCIES
// If using ImageMagick or GraphicsMagick, uncomment and set the path for the next 2 lines:
# $imagemagick_path = '/sw/bin';
# $ghostscript_path = '/sw/bin';

// Ghostscript executible name.
$ghostscript_executable = 'gs';

// If using FFmpeg to generate video thumbnails and previews, uncomment and set path:
# $ffmpeg_path = '/usr/bin';

// If using ExifTool to enable metadata writing when resources are downloaded, uncomment and set path:
# $exiftool_path = '/usr/local/bin';

// If using Antiword for text extraction and indexing of Microsoft Word document files, uncomment and set path:
# $antiword_path = '/usr/bin';

// If using pdftotext to enable PDF text extraction (http://www.foolabs.com/xpdf), uncomment and set path:
# $pdftotext_path = '/usr/bin';

// If using blender to enable 3D video (https://www.blender.org/), uncomment and set path:
# $blender_path = '/usr/bin/';

// If collection download ($collection_download=true) is enabled, uncomment and set the appropriate lines:
// Example for Linux with the zip utility:
# $archiver_path = '/usr/bin';
# $archiver_executable = 'zip';
# $archiver_listfile_argument = "-@ <";

// Example for Linux with the 7z utility:
# $archiver_path = '/usr/bin';
# $archiver_executable = '7z';
# $archiver_listfile_argument = "@";

// Example for Windows with the 7z utility:
# $archiver_path = 'C:\Program\7-Zip';
# $archiver_executable = '7z.exe';
# $archiver_listfile_argument = "@";

// Use the PHP ZIP extension instead of the $archiver or $zipcommand parameter?
$use_zip_extension = false;

// If using the Python programming language (https://www.python.org/), uncomment and set path:
# $python_path = '/usr/bin';

// If using the File Information Tool Set (FITS, https://projects.iq.harvard.edu/fits), uncomment and set path, make
//  sure the user has write access as it needs to write the log file (./fits.log), and also requires Java >1.7.
# $fits_path = '/opt/fits-1.2.0';

// Enable SWF previews if gnash_dump (gnash w/o GUI, http://www.xmission.com/~ink/gnash/gnash-dump/README.txt) is
//  compiled on the server. Ubuntu Example ./configure --prefix=/usr/local/gnash-dump --enable-renderer=agg \
//   --enable-gui=gtk,dump --disable-kparts --disable-nsapi --disable-menus  Several dependencies will be necessary,
//  according to ./configure.
# $dump_gnash_path = "/usr/local/gnash-dump/bin";

// If using Calibre to allow ebook conversion to PDF (https://calibre-ebook.com/), uncomment and set path:
# $calibre_path = "/usr/bin";

// Array of file extensions to pass to Calibre for conversion to PDF and auto thumbnail preview generation.
$calibre_extensions = array("epub", "mobi", "lrf", "pdb", "chm", "cbr", "cbz");

// If using Unoconv, a Python-based bridge to OpenOffice to allow document conversion to PDF, uncomment and set path:
# $unoconv_path = "/usr/bin";

// Array of file extensions to pass to Unoconv for conversion to PDF and auto thumb-preview generation. Default list
//  from http://svn.rpmforge.net/svn/trunk/tools/unoconv/docs/formats.txt
$unoconv_extensions = array("ods", "xls", "doc", "docx", "odt", "odp", "html", "rtf", "txt", "ppt", "pptx", "sxw", "sdw", "html", "psw", "rtf", "sdw", "pdb", "bib", "txt", "ltx", "sdd", "sda", "odg", "sdc", "potx", "key");

// If using Unoconv and Windows, uncomment and set path to Libre/OpenOffice packaged Python.
# $unoconv_python_path = '';


// STORAGE SETTINGS
// Absolute (/var/www/blah/blah) or relative path with no trailing slash to the filestore location to configure storage
//  locations to use another server for file storage.  Useful on Windows systems, where mapping filestore to a remote
//  drive or other location is not trivial.  On Unix-based systems, it is usually much easier to make '/filestore' a
//  symbolic link to another location.
# $storagedir = "/path/to/filestore";

// Remote storage URL with no trailing slash.  If you are changing $storagedir, make sure $storageurl is set.
# $storageurl = "https://my.storage.server/filestore";

// Store original files separately from ResourceSpace previews? If this setting is adjusted with resources in the
//  system, you wil need to run ../pages/tools/filestore_separation.php.
$originals_separate_storage = false;

// Enable distribution of files in the filestore more equally?  All resources with IDs ending in 1 will be stored under
//  ../filestore/1, whereas historically (set to FALSE), this would contain all resources with IDs starting with 1. If
//  enabling this after the system has been in use, run ../pages/tools/filetore_migrate.php that will relocate the
//  existing files into the new folder structure.
$filestore_evenspread = false;

// Enable forcing the system to check for a file in the old location and move it in the event that it cannot be found?
$filestore_migrate = false;


// WEB SPIDER SETTINGS
// Password required for spider.php. IMPORTANT: randomise for each new installation as resources will be readable by
//  anyone that knows this password.
$spider_password = "TBTT6FD";                           # <-------- Suggest making default much longer.

// User group that will be used to access the resource list for the spider index.
$spider_usergroup = 2;

// Access level(s) required when producing the index, options: 0 = Open, 1 = Restricted, 2 = Confidential/Hidden
$spider_access = array(0, 1);


// LOGGING SETTINGS
// Enable logging developer debug information to the debug log (filestore/tmp/debug.txt)?
$debug_log = false;

// Optional: Enable extended debugging information from backtrace (records pagename and calling functions)?
# debug_extended_info = true;

// Optional: Debug log location, used to specify a full path to debug file, ensure folder permissions allow write
//  access to both the file and the containing folder by the web service account.  As the default location is world-
//  readable, recommended for live systems to change the location to somewhere outside of the web directory.
# $debug_log_location = "d:/logs/resourcespace.log";
# $debug_log_location = "/var/log/resourcespace/resourcespace.log";

// Suppress SQL information in the debug log?
$suppress_sql_log = false;

// Enable logging of resource views for reporting purposes? General daily statistics for each resource are logged
//  anyway for the statistics graph, this option relates to specific user tracking for the more detailed report.
$log_resource_views = false;


// LANGUAGE SETTINGS
// ResourceSpace default language, uses ISO 639-1 language codes, such as en, es, etc.
//  (https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes)
$defaultlanguage = "en";

// Listing of available languages, if $defaultlanguage is not set, the browser's default language will be used.
$languages["en"] = "British English";
$languages["en-US"] = "American English";
$languages["ar"] = "العربية";      # Arabic
$languages["ca"] = "Català";       # Catalan
$languages["da"] = "Dansk";        # Danish
$languages["de"] = "Deutsch";      # German
$languages["el"] = "Ελληνικά";     # Greek
$languages["es"] = "Español";      # Spanish
$languages["es-AR"] = "Español (Argentina)"; # Argentian Spanish
$languages["fi"] = "Suomi";        # Finnish
$languages["fr"] = "Français";     # French
$languages["hr"] = "Hrvatski";     # Croatian
$languages["id"] = "Bahasa Indonesia"; # Indonesian
$languages["it"] = "Italiano";     # Italian
$languages["jp"] = "日本語";        # Japanese
$languages["nl"] = "Nederlands";   # Dutch
$languages["no"] = "Norsk";        # Norwegian
$languages["pl"] = "Polski";       # Polish
$languages["pt"] = "Português";    # Portuguese
$languages["pt-BR"] = "Português do Brasil"; # Brazilian Portuguese
$languages["ru"] = "Русский язык"; # Russian
$languages["sk"] = "Slovenčina";   # Slovenian
$languages["sv"] = "Svenska";      # Swedish
$languages["zh-CN"] = "简体字";     # Simplified Chinese

// Disable language selection options and browser detection?
$disable_languages = false;

// Display the language chooser on the bottom of each page?
$show_language_chooser = true;

// Enable browser language detection?
$browser_language = true;

// If using IPTC headers, specify any non-ASCII characters used in your local language to aid with character encoding
//  auto-detection. Several encodings will be attempted and if a character in this string is returned, then is
//  considered a match. For English, there is no need to specify anything, just an empty string and assumes UTF-8.
//  Norwegian Example: $iptc_expectedchars="æøåÆØÅ";
$iptc_expectedchars = "";


// FILE TRANSFER PROTOCOL (FTP) SETTINGS (only necessary if you plan to use the FTP batch upload feature)
// FTP server name.
$ftp_server = "my.ftp.server";

// FTP server account username.
$ftp_username = "my_username";

// FTP server account password.
$ftp_password = "my_password";

// FTP default folder.
$ftp_defaultfolder = "temp/";


// EMAIL SETTINGS
// Enable an external SMTP server for outgoing emails, e.g. Gmail? Requires $use_phpmailer=true.
$use_smtp = false;

// SMTP security setting, options: '', 'tls', or 'ssl'. For Gmail, 'tls' or 'ssl' is required.
$smtp_secure =' ';

// SMTP server hostname.
$smtp_host = ''; # Example: 'smtp.gmail.com'

// SMTP server port number.
$smtp_port = 25; # Example: 465 for Gmail using SSL.

// Enable sending credentials to the SMTP server?  FALSE = anonymous access.
$smtp_auth = true;

// SMTP account username (full email address).
$smtp_username = '';

// SMTP account password.
$smtp_password = '';

// Enable email sharing?
$email_sharing = true;

// Footer text applied to all emails.
$email_footer = "";

// Enable multilingual support for emails? Switch to TRUE if email links are not working and ASCII characters alone
//  are required, e.g. in the US.
$disable_quoted_printable_enc = false;

// Enable a user to CC oneself when sending resources or collections?
$cc_me = false;

// Enable always sending emails from the logged in user?
$always_email_from_user = false;

// Enable always CC admin user on emails from the logged in user?
$always_email_copy_admin = false;

// Enable emailing contributor when their resources have been approved, moved from Pending Submission/Review to Active?
$user_resources_approved_email = false;

// URL added to bottom of the 'emaillogindetails' template, save_user function in general.php, if blank, uses $baseurl.
$email_url_save_user = "";

// Email address to send a notification when resources expire. Requires ../batch/expiry_notification.php to be
//  executed periodically via a cron job or similar.  If not set and the script is executed, notifications will be
//  sent to resource admins or users in groups specified in $email_notify_usergroups.
# $expiry_notification_mail = "myaddress@mydomain.example";


// RESOURCE SHARING SETTINGS
// Enable resources to be emailed or shared (internally and externally)?
$allow_share = true;

// Enable theme categories to be shared?
$enable_theme_category_sharing = false;

// Use a custom CSS stylesheet when sharing externally?
$custom_stylesheet_external_share = false;

// Path to custom CSS stylesheet, $custom_stylesheet_external_share_path can be set anywhere inside the website root
// folder. Example: '/plugins/your plugin name/css/external_shares.css'
$custom_stylesheet_external_share_path = '';

// Enable those with 'restricted' access to a resource to share the resource?
$restricted_share = false;

// Enable those that have been granted open access to an otherwise restricted resource to share the resource?
$allow_custom_access_share = false;

// Enable sending an email to the address set by $email_notify when user contributed resources are submitted (status changes from "User Contributed - Pending Submission" to "User Contributed - Pending Review")?
$notify_user_contributed_submitted = true;

// Enable sending an email to the address set by $email_notify when user contributed resources are unsubmitted?
$notify_user_contributed_unsubmitted = false;

// Default value for the user select box, for example when emailing resources.
$default_user_select = "";

// Enable bypassing share.php and go straight to email?
$bypass_share_screen = false;

// Enable users to save/select predefined lists of users/groups when sharing collections and resources?
$sharing_userlists = false;

// Enable a listing of all recipients when sending resources or collections?
$list_recipients = false;

// Resource Share Expiry Controls
$resource_share_expire_days = 150; # Maximum number of days allowed for the share.
$resource_share_expire_never = true; # Allow the 'Never' option.

// Hide "Generate URL" from the resource_share.php page?
$hide_resource_share_generate_url = false;

// Collections Share Expiry Controls
$collection_share_expire_days = 150; # Maximum number of days allowed for the share.
$collection_share_expire_never = true; # Allow the 'Never' option.

// Hide "Generate URL" from the collection_share.php page?
$hide_collection_share_generate_url = false;

// Enable option to include related resources when sharing a single resource (creates a new collection)?
$share_resource_include_related = false;

// Enable display of external shares in standard internal collection view when accessed by a logged in user?
$external_share_view_as_internal = false;

// When sharing externally as a specific user group, permission x, limit the user groups shown only if they are allowed?
$allowed_external_share_groups = array();

// When sharing externally as a specific user group, permission x, honor group config options meant to respect
//  settings like $collection_download?
$external_share_groups_config_options = false;

// Enable showing only existing shares that have been shared by the user when sharing resources, not collections?
$resource_share_filter_collections = false;

// Notify on resource change. If the primary resource file is replaced or an alternative file is added, users who have
//  downloaded the resource in the last X days will be sent an email notifying them that there has been a change with
//  a link to the Resource View page, set to 0 to disable.
$notify_on_resource_change_days = 0;

// Array of social media share buttons.
$social_media_links = array("facebook", "twitter", "linkedin");


// SYSTEM EMAIL SETTINGS
// Email address where system emails appear to come from.
$email_from = "resourcespace@my.site";

// Enable user-to-user emails to come from user's address by default for better reply-to, with the user-level option of reverting to the system address?
$email_from_user = true;

// Email address to send a report to if any of the automated tests (../tests/test.php) fail. Used by Montala to
//  automatically test the ResourceSpace development trunk on a nightly basis.
# $email_test_fails_to = "example@example.com";


// CKEDITOR AND SITE CONTENT SETTINGS
// Enable WYSIWYG CKEditor rich text editor (https://ckeditor.com/)?
$enable_ckeditor = true;

// List of available CKEditor toolbars.
$ckeditor_toolbars = "'Styles', 'Bold', 'Italic', 'Underline','FontSize', 'RemoveFormat', 'TextColor','BGColor'";

// Array of CKEditor content toolbars.
$ckeditor_content_toolbars = "
    { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','RemoveFormat' ] },
    { name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','-','Undo','Redo' ] },
    { name: 'styles', items : [ 'Format' ] },
    { name: 'paragraph', items : [ 'NumberedList','BulletedList' ] },
    { name: 'links', items : [ 'Link','Unlink' ] },
    { name: 'insert', items : [ 'Image','HorizontalRule'] },
    { name: 'tools', items : [ 'Source', 'Maximize' ] }
";

// Use CKEditor for ResourceSpace content?
$site_text_use_ckeditor = false;


// HELP SETTINGS
// Enable help links on pages?
$contextual_help_links = true;

// Display Help page in a modal?
$help_modal = true;


// PLUGIN SETTINGS
// Array of active plugins.
$plugins = array('transform', 'rse_version', 'lightbox_preview', 'rse_search_notifications', 'rse_workflow');

// Optional array of plugins that cannot be enabled through the UI. Useful to lockdown system for hosting situations.
$disabled_plugins = array();

// Enable a custom message for disabled plugins? Default is the language string 'plugins-disabled-plugin-message' but this will override it.
$disabled_plugins_message = "";

// Enable the Plugins Manager?
$use_plugins_manager = true;

// Enable upload of new plugins?
$enable_plugin_upload = true;


// INDEXING AND RELATED SETTINGS
// Array of common stop keywords to ignore both when searching and when indexing.
$noadd = array();

// English language stop keywords.
$noadd = array_merge($noadd, array("", "a", "the", "this", "then", "another", "is", "with", "in", "and", "where", "how", "on", "of", "to", "from", "at", "for", "-", "by", "be"));

// Swedish language stop keywords (from http://snowball.tartarus.org/algorithms/swedish/stop.txt, 2010-11-24).
# $noadd = array_merge($noadd, array("och", "det", "att", "i", "en", "jag", "hon", "som", "han", "på", "den", "med", "var", "sig", "för", "så", "till", "är", "men", "ett", "om", "hade", "de", "av", "icke", "mig", "du", "henne", "då", "sin", "nu", "har", "inte", "hans", "honom", "skulle", "hennes", "där", "min", "man", "ej", "vid", "kunde", "något", "från", "ut", "när", "efter", "upp", "vi", "dem", "vara", "vad", "över", "än", "dig", "kan", "sina", "här", "ha", "mot", "alla", "under", "någon", "eller", "allt", "mycket", "sedan", "ju", "denna", "själv", "detta", "åt", "utan", "varit", "hur", "ingen", "mitt", "ni", "bli", "blev", "oss", "din", "dessa", "några", "deras", "blir", "mina", "samma", "vilken", "er", "sådan", "vår", "blivit", "dess", "inom", "mellan", "sånt", "varför", "varje", "vilka", "ditt", "vem", "vilket", "sitta", "sådana", "vart", "dina", "vars", "vårt", "våra", "ert", "era", "vilkas"));

// Array of separator characters to treat as white space to use when splitting keywords. Must reindex after altering if
//  data exists in the system using ../pages/tools/reindex.php. 'Space' is included by default and does not
//  need to be specified, but leave non-breaking space in.
$config_separators = array("/", "_", ".", "; ", "-", "(", ")", "'", "\"", "\\", "?", '’', '“', ' ');

// Trim characters that will be removed from the beginning or end of a string, but not the middle, when indexing.
//  Format as described in the PHP trim() documentation, leave blank for no extra trimming.
$config_trimchars = "";

// Resource field verbatim keyword regex, using the index value of [resource field], specifies regex criteria for
//  adding verbatim strings to keywords. It solves the problem, for example, indexing an entire "nnn.nnn.nnn" string
//  value when '.' are used in $config_separators.
# $resource_field_verbatim_keyword_regex[1] = '/\d+\.\d+\w\d+\.\d+/'; # Example adds 994.1a9.93 to indexed keywords
#  for field 1 and can be found using a quoted search.

// Include keywords from collection titles when indexing collections?
$index_collection_titles = true;

// Include keywords from collection creator when indexing collections?
$index_collection_creator = true;

// For fields with partial keyword indexing enabled, the minimum infix length in characters.
$partial_index_min_word_length = 3;

// Normalize keywords when indexing and searching? TRUE means that various character encodings of diacritics will be
// standardised when indexing and searching. Requires internationalization functions (PHP versions >5.3). For example,
// there are several different ways of encoding "é" (e acute) and this will ensure that a standard form of "é" will
// always be used.
$normalize_keywords = true;

// Enable that diacritics will be removed for indexing, e.g. 'zwälf' is indexed as 'zwalf', 'café' is indexed as 'cafe'.
//  The actual data is not changed, this only affects searching and indexing.
$keywords_remove_diacritics = false;

// Enable indexing the unnormalized keyword in addition to the normalized version, also applies to keywords with
//  diacritics removed. Quoted search can then be used to find matches for the original unnormalized keyword.
$unnormalized_index = false;

// Enable stemming support? Indexes stems of words only, so plural/singular (etc) forms of keywords are indexed as if
//  they are equivalent. Requires a full reindex.
$stemming = false;

// Enable indexing the 'Contributed By' field?
$index_contributed_by = false;

// Enable indexing the resource type, so searching for the resource type will work (e.g. if you have a resource of
//  type "photo", then "cat photo" will match, even if the resource metadata itself does not contain the word 'photo')?
$index_resource_type = true;

// Exclusively used for comments functionality, checking of valid anonymous email addresses entered in JS and PHP.
$regex_email = "[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}";

// Number of times a keyword must be used before it is considered eligable for suggesting, when a matching keyword is
//  not found. Set to 0 to suggest any known keyword regardless of usage, set to a higher value to ensure only popular
//  keywords are suggested.
$soundex_suggest_limit = 10;


// OFFLINE JOB QUEUE SETTINGS
// Enable offline job_queue functionality? Runs resource heavy tasks offline and sends notifications once complete.
//  Initially used by video_tracks plugin.  If TRUE, a frequent cron job or scheduled task must be run
//  ../pages/tools/offline_jobs.php. NOTE: setting may be overridden in certain cirumstances, e.g. if previews are
//  required at upload time because Google Vision facial recognition is configured with a dependent metadata field.
$offline_job_queue = false;

// Enable deleting completed jobs from the queue?
$offline_job_delete_completed = false;

// Array of valid utilities (as used by get_utility_path() function) used to create files used in offline job handlers
//  e.g. create_alt_file and create_download_file, plugins can extend this.
$offline_job_prefixes = array("ffmpeg", "im-convert", "im-mogrify", "ghostscript", "im-composite", "archiver");

// Default time in days of a temp download file created by the job queue after which it will be deleted by another job.
$download_file_lifetime = 14;


// REPORTING SETTINGS
// Array of default reporting time periods in days.
$reporting_periods_default = array(7, 30, 100, 365);

// Display resource hit counts?
$show_hitcount = false;

// Use hit count functionality to track downloads rather than resource views?
$resource_hit_count_on_downloads = false;

// Enable Admin/System bug report option?
$team_centre_bug_report = false;


// KEYBOARD CONTROL CODE SETTINGS
// Enable keyboard navigation using left and right arrows to browse through resources in view/search/preview modes?
$keyboard_navigation = true;

// Previous Resource keyboard control code, default: left arrow.
$keyboard_navigation_prev = 37;

// Next Resource keyboard control code, default: right arrow.
$keyboard_navigation_next = 39;

// <----?
$keyboard_navigation_pages_use_alt = false;

// Add Resource to collection keyboard control code, default 'a'
$keyboard_navigation_add_resource = 65;

// Previous Page in document preview keyboard control code, default ','
$keyboard_navigation_prev_page = 188;

// Next Page in document preview keyboard control code, default '.'
$keyboard_navigation_next_page = 190;

// View All results keyboard control code, default '/'
$keyboard_navigation_all_results = 191;

// Toggle Thumbnails in collections frame keyboard control code, default 't'
$keyboard_navigation_toggle_thumbnails = 84;

// View All resources from current collection keyboard control code, default 'v'
$keyboard_navigation_view_all = 86;

// Zoom To/From preview keyboard control code, default 'z'
$keyboard_navigation_zoom = 90;

// Close Modal keyboard control code, default 'ESC'
$keyboard_navigation_close = 27;

// Enable arrow keys jump from picture to picture in preview_all mode (horizontal only)?
$keyboard_scroll_jump = false;

// Search Video keyboard navigation code.
$keyboard_navigation_video_search = false;

// View Video keyboard navigation code.
$keyboard_navigation_video_view = false;

// Preview Video keyboard navigation code.
$keyboard_navigation_video_preview = false;

// Enable playing video backwards and keyboard navigation code (in development), default 'j'
$video_playback_backwards = false;
$keyboard_navigation_video_search_backwards = 74;

// Play/Pause video keyboard navigation code, default 'k'
$keyboard_navigation_video_search_play_pause = 75;

// Play video forward keyboard navigation code, default 'l'
$keyboard_navigation_video_search_forwards = 76;


// DISK SPACE QUOTA SETTINGS (requires running ../pages/tools/check_disk_usage.php)
// Quota size for a set amount of disk space to ResourceSpace in GB (decimal, not binary, so 1000 multiples).
# $disksize = 150;

// Percentage (1 to 100) of disk space used before notification is sent.
# $disk_quota_notification_limit_percent_warning = 90;

// Interval in hours to wait before sending another disk space quota warning.
# $disk_quota_notification_interval = 24;

// GB of disk space left before uploads are disabled, causes disk space to be checked before each upload attempt.
# $disk_quota_limit_size_warning_noupload = 10;

// Email address for disk quota notifications.
$disk_quota_notification_email = '';


// SPECIAL ADMINISTRATIVE SETTINGS
// Enable allowing admin users to download the config.php file, user, and configuration data from your server,
//  optionally including resource data? Requires offline jobs to be enabled. Most data will be obfuscated, unless
//  $system_download_config_force_obfuscation=false. Note: due to the highly configurable nature of ResourceSpace, this
//  obfuscation cannot be guaranteed to remove all sensitive data and care must be taken to secure exported data.
$system_download_config = false;

// If $system_download_config=true, force abfuscation of downloaded data?
$system_download_config_force_obfuscation = true;

// Enable creation of new ResourceSpace text entries from Manage Content? This is intended for developers who create
//  custom pages or hooks and need to have more manageable content.
$site_text_custom_create = false;

// EXPERIMENTAL: Enable email notification of PHP errors to $email_notify?
$email_errors = false;

// EXPERIMENTAL: If $email_errors=true, alternative address for email notification of PHP errors.
$email_errors_address="";

// Ability to connect to a remote system for the configuration loading. Can be used to create a multi-instance setup,
//  where one ResourceSpace installation can connect to different databases/set different filestore paths depending on
//  the URL, and be driven from a central management system that provides the configuration. The last 33 characters of
//  the returned config must be an MD5 hash and the key and the previous characters up until, but not including, the
//  hash. Example, on the remote system that serves the configuration, to remotely configure the application name:
#       $remote_config_key="abcdef12345";
#       $config = '$applicationname = "Test Remote Config ";';
#       echo $config . "#" . md5($remote_config_key . $config);
# $remote_config_url = "http://remote-config.mycompany.com";
# $remote_config_key = ""; # The baseurl will be hashed with this key and passed as an &sign= value.

// File System Template. Allows a system to contain an initial batch of resources that are stored elsewhere and read
//  only. Used by Montala for the ResourceSpace trial account templates, so each templated installation does not need
//  to completely copy all the sample assets. Enables file system template for resource IDs less than this number. Set
//  the system so the user created resources start at 1000. IMPORTANT: once $fstemplate_alt_threshold is set, run SQL
//  query: "alter table resource auto_increment = $fstemplate_alt_threshold"
$fstemplate_alt_threshold = 0;

// Alternative filestore location for the sample files; the location of the template installation.
$fstemplate_alt_storagedir = "";
$fstemplate_alt_storageurl = "";

// Scramble key used by the template installation, paths must be scrambled using this instead for the sample images.
$fstemplate_alt_scramblekey = "";

// Array of data joins, developer's tool to allow adding additional resource field data to the resource table for use
//  in search displays. Example: $data_joins=array(13); to add the expiry date to the general search query result.
$data_joins = array();

// Array of users that can update very low level configuration options, for example debug_log.
$system_architect_user_names = array('admin'); # WARNING: for experienced technical users and ResourceSpace providers.


//----USER SETTINGS-----------------------------------------------------------------------------------------------------
// Enable asking users to opt-in to registering to access the system. This can address data protection law
//  requirements  (e.g. GDPR).
$user_registration_opt_in = true;

// Enable users to request accounts?
$allow_account_request = true;

// Enable sending a confirmation email to the account requester?
$account_request_send_confirmation_email_to_requester = true;

// Enable users to change their passwords?
$allow_password_change = true;

// Should the system allow users to request new passwords via the login screen?
$allow_password_reset = true;

// Enable requiring terms on first login?
$terms_login = false;

// Enable the 'Keep Me Logged in at this Workstation' option on the login form? If selected, a 100 day expiry time is
//  set on the cookie.
$allow_keep_logged_in = true;

// Enable 'Remember Me' checked by default?
$remember_me_checked = true;

// Length of a user session in minutes, used for user sessions per day statistics and auto logout if
//  $session_autologout=true.
$session_length = 300;

// Automatically log a user out at the end of a session (a period of idleness equal to $session_length above)?
$session_autologout = true;

// Enable a randomised session hash?  Each new session is completely unique each login and may be more secure as the
//  hash is less easy to guess, but only one user can use a given user account at any one time.
$randomised_session_hash = false;

// Enable browsers to save the login information on the login form?
$login_autocomplete = true;

// Display the title of the resource being viewed in the browser title bar?
$show_resource_title_in_titlebar = false;

// Enable ignoring case when validating username at login?
$case_insensitive_username = false;

// Enable users to create new collections?
$collection_allow_creation = true;

// Should a user that has contributed a resource always have open access to it?
$open_access_for_contributor = false;

// Should a user that has contributed a resource always have edit access to it, even if the resource is live?
$edit_access_for_contributor = false;

// Enable preventing granting open access if a user has edit permissions? TRUE will allow group permissions ('e*' and
//  'ea*') to determine editability.
$prevent_open_access_on_edit_for_active = false;

// Enable permission checking before showing 'Edit All' link in the Collection Bar and on Manage My Collections page?
//  Performance hit if there are many resources in collections.
$edit_all_checkperms = false;

// Remove archived resources from collection results, unless user has e2 (admins) permission?
$collections_omit_archived = false;


// USER GROUP SETTINGS
// Global permissions that will be prefixed to all user group permissions, handy for setting global options for fields.
$global_permissions = "";

// Global permissions that will be removed from all user group permissions, useful for temporarily disabling
//  permissions globally, e.g. to make the system readonly during maintenance. Example 'read only' mode:
//  $global_permissions_mask = "a, t, c, d, e0, e1, e2, e-1, e-2, i, n, h, q";
$global_permissions_mask = "";

// Array of user group IDs for which the Knowledge Base will launch on login, until dismissed.
$launch_kb_on_login_for_groups = array();

// Enable stricter adherence to the idea of "children only"? 'U' permission allows management of users in the current
//  group, as well as children groups.
$U_perm_strict = false;

// Enable user attach to include 'smart group option', different from the default "users in group" method that will
//  still be available?
$attach_user_smart_groups = true;


// USER MANAGEMENT SETTINGS
// Default group ID to add new users.
$default_group = 2;

// Enable the purge users function?
$user_purge = true;

// Enable disabling rather than deleting inactive users?
$user_purge_disable = false;

// Automatically disable inactive users after a set number of days (requires cron.php task to be setup).
$inactive_user_disable_days = 0;

// Display the fullname of the user who created the account when editing user?
$user_edit_created_by = false;

// Display the user email address if $user_edit_created_by=true?
$user_edit_created_by_email = false;

// Display the full name of the user who approved the account when editing user?
$user_edit_approved_by = false;

// Display the user email address if $user_edit_approved_by=true?
$user_edit_approved_by_email = false;

// Display User Ref on the User Edit Page in the header? Example output: Edit User 12
$display_useredit_ref = false;

// Display group filter and user search at top of team_user.php?
$team_user_filter_top = false;


// USER PASSWORD SETTINGS (must be met when a user or admin creates a new password)
// Password minimum length in characters.
$password_min_length = 7;

// Password minimum number of alphabetical characters (a-z, A-Z) in any case.
$password_min_alpha = 1;

// Password minimum number of numeric characters (0-9).
$password_min_numeric = 1;

// Password minimum number of UPPER CASE alphabetical characters (A-Z).
$password_min_uppercase = 0;

// Password minimum number of 'special', non alphanumeric characters (!@$%& etc.).
$password_min_special = 0;

// Length of time in days that passwords expire, set to 0 for no expiration.
$password_expiry = 0;

// Number of days a reset password link is valid for and based on server time, default is 1 day. The link will always
//  be valid for the remainder of the current server day. If set to 0, the link will be valid only on the same day,
//  i.e. until midnight from the time the link is generated. If set to 1, the link will also be valid all the next day.
$password_reset_link_expiry = 1;

// Enable not showing any notification text if a password reset attempt fails to find a valid user? FALSE means
//  potential hackers can discover valid email addresses.
$hide_failed_reset_text = true;

// Time to to wait in seconds before returning a 'Password Incorrect' message for logins or 'e-mail not found' message
//  for the Request New Password page.  This can help to deter brute force attacks, trying to find user's passwords or
//  email addresses in use.
$password_brute_force_delay = 4;

// Number of failed login attempts per IP address until a temporary ban is placed on the IP address to help prevent
//  dictionary attacks.
$max_login_attempts_per_ip = 20;

// Number of failed login attempts per username until a temporary ban is placed on this IP address.
$max_login_attempts_per_username = 5;

// Time in minutes user must wait after failing the login $max_login_attempts_per_ip or
//  $max_login_attempts_per_username times.
$max_login_attempts_wait_minutes = 10;

// Display a friendly error to user, instead of a HTTP 403 error if IP address is not within the permitted range?
$iprestrict_friendlyerror = false;


// ANONYMOUS USER SETTINGS
// Enable anonymous access?  Set to the 'username' of the user who will represent all your anonymous users. Note that
//  collections will be shared among all anonymous users; therefore, best to turn off all collections functionality
//  for the anonymous user.
# $anonymous_login = "guest";

// When anonymous access is on, show login in a modal?
$anon_login_modal = false;

// Display the login panel for anonymous users?
$show_anonymous_login_panel = true;

// Use an anonymous user session collection?
$anonymous_user_session_collection = true;

// Set array to enable domain linked anonymous access to allow different anonymous access users for different domains.
//  The usernames are the same rules for just a single anonymous account, but you must match them against the full
//   domain $baseurl that they will be using.  Collections will be shared among all anonymous users for each domain; therefore, usually best to turn off all collections functionality for the anonymous user.
# $anonymous_login = array(
#    "http://example.com" => "guest",
#    "http://test.com" => "guest2"
#    );

// EXPERIMENTAL: Set user group ID to automatically create a separate user for each anonymous session and log them in,
//  use with caution!
# $anonymous_autouser_group = 2;


// USER PREFERENCES SETTINGS
// Enable user preferences?
$user_preferences = true;

// Enable user admins to receive notifications about user management changes, e.g. account requests?
$user_pref_user_management_notifications = false;

// Enable system admins to receive notifications about system events, e.g. low disk space?
$user_pref_system_management_notifications = true;

// Enable receiving emails and new style system notifications where appropriate?
$email_user_notifications = false;

// Enable receiving emails and new style system notifications where appropriate?
$email_and_user_notifications = false;

// Enable display of notification popups for new messages?
$user_pref_show_notifications = true;

// Enable display of a daily digest? Sets the default setting for a daily email digest of unread system notifications.
$user_pref_daily_digest = false;

// Enable setting the messages as read once the email is sent?
$user_pref_daily_digest_mark_read = true;

// Automatically send a digest of all messages if a user has not logged on for the specified number of days.
$inactive_message_auto_digest_period = 7;

// Accompanying user preference option?
$user_pref_inactive_digest = false;

// Enable receiving notifications about resource management, e.g. archive state changes?
$user_pref_resource_notifications = false;

// Enable receiving notifications about resource access, e.g. resource requests?
$user_pref_resource_access_notifications = false;

// Administrator default for receiving notifications about resource access, e.g. resource requests. Cannot use
//  $user_pref_resource_access_notifications, since this will use the setting of requesting user.
$admin_resource_access_notifications = false;

// Display full username column in My Messages/Actions pages?
$messages_actions_fullname = true;

// Display usergroup column in My Messages/Actions area?
$messages_actions_usergroup = false;


// USER ACTION SETTINGS
// Enable user action functionality?
$actions_enable = false;

// If $actions_enable=false, array to enable actions only for users with certain permissions, to enable actions based on users having more than one permission, separate with a comma.
$actions_permissions = array("a", "t", "R", "u", "e0");

// Enable resource request action?
$actions_resource_requests = true;

// Enable account request approval action?
$actions_account_requests = true;

// Enable resource review action?
$actions_resource_review = true;

// <------????
$actions_notify_states = "-1";

// Resource types to exclude from notifications.
$actions_resource_types_hide = "";

// User group IDs to exclude from notifications.
$actions_approve_hide_groups = "";

// Display action links, e.g. user requests, resource requests, in a modal?
$actions_modal = true;


// USER AUTO-ACCOUNT SETTINGS
// Enable user auto-application?  FALSE by default and applications for new user accounts will be sent as emails,
//  TRUE means user accounts will be created, but will need to be approved by an administrator before user can log in.
$user_account_auto_creation = false;

// User group for auto-created accounts. $registration_group_select allows users to select the group themselves.
$user_account_auto_creation_usergroup = 2;

// Enable automatically approving ALL account requests created via $user_account_auto_creation?
$auto_approve_accounts = false;

// If $user_account_auto_creation=true, array to enable automatically approving accounts that have emails with given
//  domain names?  Example: $auto_approve_domains = array("mycompany.com", "othercompany.org");  Do not use with
//  $auto_approve_accounts=true, as it will override this parameter and approve all accounts regardless of e-mail domain.
//  Optional additional feature to place users in groups depending on email domain. Example:
//  $auto_approve_domains = array("mycompany.com" => 2, "othercompany.org" => 3); where 2 and 3 are the ID numbers for
//  the respective user groups.
$auto_approve_domains = array();

// Enable usernames to be created based on full name (eg. John Mac -> John_Mac) if $user_account_auto_creation=true?
$user_account_fullname_create = false;

// Display an error when someone tries to request an account with an email already in the system?  Hiding this error
//  is useful if you consider this to be a security issue, i.e. exposing that the email is linked to an account.
$account_email_exists_note = true;


// CUSTOM USER SETTINGS
// List of additional custom fields that are collected and emailed when new users apply for an account, comma separated.
# $custom_registration_fields = "Phone Number,Department";

// List of additional fields that are required.
# $custom_registration_required = "Phone Number";

// Custom field display formatting, options:
//  1: Normal text box, default.
//  2: Large text box.
//  3: Dropdown box, set options using $custom_registration_options["Field Name"] = array("Option 1", "Option 2",
//      "Option 3");
//  4: HTML block, e.g. help text paragraph, set HTML using $custom_registration_html["Field Name"] = "<b>Some HTML</b>";
//      Optionally, you can add the language to this, ie. $custom_registration_html["Field Name"]["en"] = ...
//  5: Checkbox, set options using $custom_registration_options["Field Name"] = array("0:Option 1", "1:Option 2",
//      "Option 3"); where 0: and 1: are unchecked and checked(respectively) by default, if not specified, then
//      assumed unchecked.  Example:
//      $custom_registration_options["Department"] = array("0:Human Resources", "1:Marketing", "1:Sales", "IT");
//      If this field is listed in $custom_registration_required, then the user will be forced to check >=1 option.
# $custom_registration_types["Department"] = 1;

// Enable user group to be selected as part of user registration?  User groups available for user selection must be
//  specified using 'Allow Registration Selection' option on each user group in System Setup. Useful when
//  $user_account_auto_creation = true;
$registration_group_select = false;

// Enable 'custom' access level? Allows fine-grained control over access to resources.  May want to disable if you are
//  using metadata based access control, search filter on the user group.
$custom_access = true;

// Default level for Custom Access. Will only work for resources that have not been set to custom previously;
//  otherwise, they will show their previously set values. Options: 0 - Open, 1 - Restricted, or 2 - Confidential.
$default_customaccess = 2;


//----WEBPAGE FORMATTING SETTINGS---------------------------------------------------------------------------------------
// GENERAL FORMATTING SETTINGS
// Enable the responsive UI?
$responsive_ui = true;

// Enable the Retina mode to use the next size up when rending previews and thumbnails for a more crisp display on high
//  resolution screens? Note: uses much more bandwidth.
$retina_mode = false;

// Display standard pages, e.g. resource requests, in a modal?
$modal_default = false;

// Initialize array for classes to be added to the HTML <body> element.
$body_classes = array();

// Use the new tab ordering system? This will sort the tabs by the order value set in System Setup.
$use_order_by_tab_view = false;

// Allows for themes with a taller header than standard to still be fully visible in System Setup.
$admin_header_height = 120;

// Remove the line that separates collections panel menu from resources?
$remove_collections_vertical_line = false;

// Enable dropdown selectors for the Display and Results Display menus?
$display_selector_dropdowns = false;

// Enable per-page dropdown without $display_selector_dropdown=true. Useful to use the display selector icons with
//  per-page dropdowns.
$perpage_dropdown = true;

// Enable the pager dropdown?
$pager_dropdown = false;

// Length of time in milliseconds until the loading popup appears during an AJAX request.
$ajax_loading_timer = 500;

// Use the Chosen library for rendering dropdowns with improved display and search capability for large dropdowns?
$chosen_dropdowns = false;

// Number of options that must be present before including search capability with Chosen dropdowns.
$chosen_dropdowns_threshold_main = 10;

// Maximum number of words shown before more/less link is shown, used in the resource log.
$max_words_before_more = 30;


// PAGE HEADER SETTINGS
// Replace header logo with text, application name, and description?
$header_text_title = false;

// Path to the page header favicon.
$header_favicon = "gfx/interface/favicon.png";

// Is the header logo a link to the homepage?
$header_link = true;

// Header size class.
$header_size = "HeaderMid"; # Options: 'HeaderSmall', 'HeaderMid', or 'HeaderLarge'

// Should the top bar remain present when scrolling down the page?
$slimheader_fixed_position = false;

// Display username to the right of the user menu icon in the header?
$header_include_username = false;

// Custom source location for the header image (includes baseurl, requires leading "/"). Will default to the ResourceSpace logo if left blank. Recommended image size: 350px(X) x 80px(Y)
$linkedheaderimgsrc = "";

// Use a custom header logo link to another URL by uncommenting and set:
# $header_link_url = "https://my-alternative-header-link";

// Display public collections page in header and omit from Themes and Manage Collections?
$public_collections_header_only = false;

// Frequency in seconds which the page header will poll for new messages for the user, set to 0 to disable.
$message_polling_interval_seconds = 10;

// Include ResourceSpace version header in page View Source?
$include_rs_header_info = true;

// Specify custom header colours by setting the next two lines:
$header_colour_style_override = '';
$header_link_style_override = '';


// PAGE FOOTER SETTINGS
// Display extra Home, About, and Contact Us links in the page footer?
$bottom_links_bar = false;

// Display the performance metrics in the footer for debug?
$config_show_performance_footer = false;


// SLIDESHOW SETTINGS
// Path to home images, such as "gfx/homeanim/mine/", files should be numbered sequentially and will be auto-counted.
$homeanim_folder = "gfx/homeanim/gfx";

// Custom slideshow image size in pixels, honoured by the transform plugin to allow easy replacement of images. Can be
//  used as config override in conjunction with $homeanim_folder (for large images, may want to set $home_themeheaders,
//  $home_themes, $home_mycollections, and $home_helpadvice to FALSE).
# $home_slideshow_width = 517;
# $home_slideshow_height = 350;

// Enable the small (old) slideshow mode?
$small_slideshow = true;

// Enable the big (fullscreen) slideshow mode?  If TRUE, configure larger slideshow images with $home_slideshow_width
//  and $home_slideshow_height, and regenerate slideshow images using the transform plugin. Recommended to be used with
//  the slim header.
$slideshow_big = false;

// Number of seconds for slideshow to wait before changing image, must be >1.
$slideshow_photo_delay = 5;

// Enable using a random static image from the available slideshow images? Requires $slideshow_big=true;
$static_slideshow_image = false;


// HOMEPAGE SETTINGS
// Default homepage when not using themes as the homepage. Search results example:
//  $default_home_page = "search.php?search=example";
$default_home_page = "home.php";

// Specify custom colours for homepage elements (site text, dash tiles, simple search).
$home_colour_style_override = '';

// Use the themes page as the homepage?
$use_theme_as_home = false;

// Use the recent page as the homepage?
$use_recent_as_home = false;

// Move the welcome text into the homepage picture panel? Stops text from falling behind other panels.
$welcome_text_picturepanel = false;

// Hide the welcome text?
$no_welcometext = false;

// Enable setting cookies at root? For now, this is implemented for the colourcss cookie to preserve selection between
//  pages, team, and plugin pages.  Probably requires the user to clear cookies.
$global_cookies = false;


// TOP NAVIGATION BAR SETTINGS
// Display a 'Recent' link in the top navigation?
$recent_link = true;

// Display a 'Help and Advice' link in the top navigation?
$help_link = true;

// Display a 'Search Results' link in top navigation?
$search_results_link = true;

// Display a 'My Collections' link in the top navigation? Permission 'b' is needed for Collection Manage to be displayed.
$mycollections_link = false;

// Display a 'My Requests' link in the top navigation?
$myrequests_link = false;

// Display a 'Research Request' link in the top navigation?
$research_link = true;

// Display a 'Themes' link in top navigation if themes is enabled?
$themes_navlink = true;

// Display a 'My Contributions' link in the top navigation for admin (permission C)?
$mycontributions_link = false;

// Hide 'My Contributions' link from regular users?
$mycontributions_userlink = true;

// Display an upload link in the top navigation if 't' and 'c' permissions for the current user?
$top_nav_upload = true;

// Display an upload link in the top navigation in addition to 'My Contributions' if 'd' permission for the current user?
$top_nav_upload_user = false;

// If $top_nav_upload=true, the upload type. Options: 'plupload', 'ftp', and 'local'
$top_nav_upload_type = "plupload";

// Display a 'Public Collections' link in the top navigation?
$public_collections_top_nav = false;

// Display a 'Contact Us' link?
$contact_link = true;
$nav2contact_link = false;

// Display an 'About Us' link?
$about_link = true;

// Custom top navigation links. You can add as many panels as you like and be numbered sequentially starting from zero
//  (0, 1, 2, 3, etc.). URL should be absolute or include $baseurl, because a relative URL will not work from the Team
//  Center. Since configuration is prior to $lang availability, use a special syntax prefixing the string "(lang)" to
//  access $lang['mytitle'].
# $custom_top_nav[0]["title"] = "Example Link A";
# $custom_top_nav[0]["link"] = "$baseurl/pages/search.php?search=a";
# $custom_top_nav[1]["title"] = "Example Link B";
# $custom_top_nav[1]["link"] = "$baseurl/pages/search.php?search=b";


// GENERAL FORMATTING SETTINGS
// Use day-month-year format instead of month-day-year?
$date_d_m_y = true;

// Display year in a four digit format?
$date_yyyy = false;

// Separator to use when rendering date range field values.
$range_separator = " / ";

// Enable EDTF format when rendering date range field inputs, e.g. 2004-06/2006-08, 2005/2006-02? (see
//  http://www.loc.gov/standards/datetime/pre-submission.html#interval)
$daterange_edtf_support = false;

// Display front end popup error when an invalid date value or format is entered, e.g. 31-02-2020 or bad partial
//  dates?  Configuration could be removed, once a more subtle way of erroring is found.
$date_validator = false;

// Enable using decimal (KB, MB, GB in multiples of 1000) vs. binary (KiB, MiB, GiB, TiB in multiples of 1024).
$byte_prefix_mode_decimal = true;

// Default font selection.
$global_font = "WorkSans";

// Display tabbed panels for Resource View, Metadata, Location, Comments, Related Collection, Related Galleries,
//  Related Resources, and Search for Similar?
$view_panels = false;


// HOME DASH AND TILE SETTINGS
// Enable home dash/tile functionality, recommended?
$home_dash = true;

// Available tile styles per type.
$tile_styles['srch'] = array('thmbs', 'multi', 'blank'); # Search tile.
$tile_styles['ftxt'] = array('ftxt'); # Fixed text tile.
$tile_styles['conf'] = array('blank');
$tile_styles['fcthm'] = array('thmbs', 'multi', 'blank');

// Use the default dash for anonymous users on the homepage with no drag-n-drop functionality?
$anonymous_default_dash = true;

// Use shadows on all tile content with built-in support for transparent tiles?
$dash_tile_shadows = false;

// Revoke user permissions for the dash and the dash admin manages a single dash for all users? Only those with admin privileges can modify the dash and must be done from the Admin>Manage Dash Tiles.
$managed_home_dash = false;

// Enable dash administrators to have their own dash, while all other users have the managed dash ($managed_home_dash=true)?
$unmanaged_home_dash_admins = false;

// Enable dash tile colour picker? If TRUE, and no colour options set, a jsColor picker will be shown.
$dash_tile_colour = true;

// Available dash tile colour picker colours.
$dash_tile_colour_options = array(); # Example: array('0A8A0E' => 'green', '0C118A' => 'blue');


//----SEARCH SETTINGS---------------------------------------------------------------------------------------------------
// Maximum number of results to return from a search.
$max_results = 200000;

// Year of the earliest resource record, used for the date selector on the search form. Unless you are adding existing resources to the system, best to set this to the current year at the time of installation.
$minyear = 1980;

// Search on day, in addition to month and year?
$searchbyday = false;

// Enable dates to be set within date ranges? Ensure to allow 'By Date' used in Advanced Search if required.
$daterange_search = false;

// Enable searching the archive and display a count with every search?  WARNING: Performance penalty.
$archive_search = false;

// Number of results to trigger the 'suggestion' feature, -1 to disable.  WARNING: Significant performance penalty for
//  enabling this feature as it attempts to find the most popular keywords for the entire result set and not recommended
//  for large systems.
$suggest_threshold = -1;

// Default resource types to use for searching, leave empty for all.
$default_res_types = "";

// Enable titles on the Search page that help describe the current context?
$search_titles = false;

// Enable whether all/additional keywords should be displayed in search titles (ex. "Recent 1000 / pdf")?
$search_titles_searchcrumbs = false;

// Enable whether field-specific keywords should include their shortnames in searchcrumbs if
//  $search_titles_searchcrumbs=true; ex. "originalfilename:pdf"?
$search_titles_shortnames = false;

// Should the resources that are in the archive state "User Contributed - Pending Review" (-1) be visible in the main
//  searches as with resources in the active state? The resources will not be downloadable, except to the contributer
//  and those with edit capability to the resource.
$pending_review_visible_to_all = false;

// Should the resources that are in the archive state "User Contributed - Pending Submission" (-2) be searchable?
//  Otherwise, users can search only for their own resources Pending Submission.
$pending_submission_searchable_to_all = false;

// When searching, include themes at the top?
$search_includes_themes = false;

// When searching, include public collections at the top?
$search_includes_public_collections = false;

// When searching, include user collections?
$search_includes_user_collections = false;

// When searching, include resources?
$search_includes_resources = true;

// Should the 'Clear' button leave collection searches off by default?
$clear_button_unchecks_collections = true;

// Enable Find Similar search?
$enable_find_similar = true;

// Enable numeric keyword search, then the resource with the matching ID will be shown?  If FALSE, the search for the
//  number provided will be performed as with any keyword. However, if a resource with a matching ID number if found,
//  then this will be shown first.
$config_search_for_number = false;

// When searching collections, return results based on the metadata of the resources inside as well?
$collection_search_includes_resource_metadata = false;

// Array to separate some resource types in searchbar selection boxes.
$separate_resource_types_in_searchbar = array();

// Enable searching all workflow states? Does not work with $advanced_search_archive_select=true (Advanced Search
//  status searching) as this option removes the workflow selection altogether. IMPORTANT: Feature gets disabled when
//  requests ask for a specific archive state (e.g. View Deleted Resources or View Resources in Pending Review).
$search_all_workflow_states = false;

// Enable moving the 'Search' button before the 'Clear' button?
$swap_clear_and_search_buttons = false;

// Enable limiting recent search to resources uploaded in the last X days?
$recent_search_period_select = false;

// Array of recent search results limit in days.
$recent_search_period_array = array(1, 7, 14, 60);

// Enable recent link to use recent X days instead of recent X resources?
$recent_search_by_days = false;

// Default recent search limit in days.
$recent_search_by_days_default = 60;

// Enable refining the search string parsing? Disabled by Dan due to an issue I was unable to replicate (Tom).
$use_refine_searchstring = false;

// Display the disk usage for search results?
$show_searchitemsdiskusage = true;

// When using the "View These Resources as a Result Set" link, display the original resource in search result?
$related_search_show_self = false;

// Enable the addition of 'saved searches' to collections?
$allow_save_search = true;

// Field to display in searchcrumbs for a related search, defaults to filename. If set to a different field and the
//  value is empty, fallbacks to filename.
$related_search_searchcrumb_field = 51;

// Enable on-way keyword relationships? By default, keyword relationships are two-way (if "tiger" has a related
//  keyword "cat", then a search for "cat" also includes "tiger" matches). TRUE means that if "tiger" has a related
//  keyword "cat", then a search for "tiger" includes "tiger", but does not include "cat" matches.
$keyword_relationships_one_way = false;

// Number of keywords included in the search when a single keyword expands via a wildcard. Setting this too high may
//  cause performance issues.
$wildcard_expand_limit = 50;

// Should all manually entered keywords (e.g. basic search and 'all fields' search on Advanced Search) be treated as
//  wildcards? Example: "cat" will always match "catch", "catalogue", "category" with no need for an asterisk.
//  WARNING: option could cause search performance issues due to the hugely expanded searches that will be performed.
//  It will also cause some other features to be disabled: related keywords and quoted string support.
$wildcard_always_applied = false;

// Enable prepending wildcard to the keyword for searches?
$wildcard_always_applied_leading = false;


// SPECIAL SEARCH SETTINGS
// Enable special searches to honor resource type settings?
$special_search_honors_restypes = false;

// Enable SmartSearch to override $access rules when searching?
$smartsearch_accessoverride = true;

// Enable updated search filter functionality? Allows for simpler setup of more advanced search filters.  Once enabled
//  the filters will gradually be updated as users search. To update all the filter immediately, run
//  ../upgrade/scripts/005_migrate_search_filters.php.
$search_filter_nodes = false;

// Enable making search filter strict to prevent direct access to view/preview page? Slight performance penalty on
//  larger search results. EXPERIMENTAL: Set to 2 in order to emulate single resource behaviour in search. Prevents
//  search results that are not accessible from showing up.
$search_filter_strict = true;

// EXPERIMENTAL: Enable two-pass mode for search results performance enhancement? The first query returns only the
//  necessary number of results for the current search results display. The second query is the same, but returns only
//  a count of the full result set, which is used to pad the result array to the correct size so counts display
//  correctly. Large volumes of resource data are not passed around unnecessarily that can significantly improve
//  performance on systems with large data sets.
$search_sql_double_pass_mode = true;

// Enable custom access to override search filters. For this resource, if custom access has been granted for the user
//  or group, nullify the filter for this particular.
$custom_access_overrides_search_filter = false;


// SIMPLE SEARCH SETTINGS
// Enable simpler search in header, expanding for the full box?  Work in progress, in development for larger
//  ResourceSpace 9.0 release. Some functions may not work currently.
$header_search = false;

// Make the Simple Search box even more basic, with just the single search box.
$basic_simple_search = false;

// For recent_link and view_new_material, and use_recent_as_home, the quantity of resources to return.
$recent_search_quantity = 1000;

// Honor display condition settings on the Simple Search bar for the included fields?
$simple_search_display_condition = array();

// Array of user IDs to limit filter searches to resources uploaded by users with the specified IDs. '-1' is an alias
//  to the current user. Example: to filter search results to only include resources uploaded by the current user and
//  the admin user (by default user ID 1) set: $resource_created_by_filter = array(-1, 1); This is used for the
//  ResourceSpace demo installation.
# $resource_created_by_filter = array();


// SIMPLE SEARCH DISPLAY SETTINGS
// Display the Country field in the Simple Search box (requires a field with the short name 'country')?
$country_search = false;

// Enable Resource ID search blank in Simple Search Box? (only needed if $config_search_for_number=true).
$resourceid_simple_search = false;

// Enable date option in Simple Search box?
$simple_search_date = true;

// Display an "all" toggle checkbox for resource types in the Simple Search box?
$searchbar_selectall = false;

// Move the 'Search' and 'Clear' buttons to the bottom of the Simple Search box?
$searchbar_buttons_at_bottom = true;

// Hide the main Simple Search field in the searchbar if using only Simple Search fields for the Searchbar?
$hide_main_simple_search = false;

// Display keywords as pills on Simple Search? Use tab to create new tags or pills, full text strings are also accepted as a pill.
$simple_search_pills_view = false;

// Display a 'View New Material' link in the Simple Search box?
$view_new_material = false;

// Reset the Simple Search box after a search?
$simple_search_reset_after_search = false;

// Enable auto-completion in the Simple Search box?
$autocomplete_search = true;

// Number of auto-complete search items to display.
$autocomplete_search_items = 15;

// Minimum number of times a keyword appears in metadata before it qualifies for inclusion in auto-complete. Helps to
//  hide spurious values.
$autocomplete_search_min_hitcount = 10;

// Enable showing dynamic dropdows as normal dropdowns on the Simple Search? If FALSE, a standard text box is shown.
$simple_search_show_dynamic_as_dropdown = true;

// Number of options that must be present before including search capability with Chosen dropdowns in Simple Search?
$chosen_dropdowns_threshold_simplesearch = 10;

// When multiple dropdowns are used in the Simple Search box, should selecting something from one or more dropdowns
//  limit the options available in the other dropdowns automatically? Adds a performance penalty, so FALSE by default.
$simple_search_dropdown_filtering = false;


// ADVANCED SEARCH
// Hide Advanced Search in the Simple Search box?
$advancedsearch_disabled = false;

// Advanced Search Options: Defaults (all false) shows Advanced Search in the Simple Search box, but not the homepage or //  top navigation.  To disable Advanced Search, set:
# $advancedsearch_disabled = true;
# $home_advancedsearch = false;
# $advanced_search_nav = false;

// Display 'Contributed By' on Advanced Search (ability to search for resources contributed by a specific user)?
$advanced_search_contributed_by = true;

// Display a Media section on Advanced Search?
$advanced_search_media_section = true;

// Display additional 'Clear' and 'Show Results' buttons at top of the Advanced Search page?
$advanced_search_buttons_top = false;

// Enable user to select archive state in Advanced Search?
$advanced_search_archive_select = true;

// Default Advanced Search search on, options: "Global", "Collections" or resource type id (e.g. 1 for photo in
//  default installation, can be comma separated to enable multiple selections.
$default_advanced_search_mode = "Global";


// SORTING SETTINGS
// Default sort order, options: 'date', 'colour', 'relevance', 'popularity', and 'country'.
$default_sort = "relevance";

// Enable sorting resources by colour?
$colour_sort = true;

// Enable sorting resources by popularity?
$popularity_sort = true;

// Enable sorting resources randomly?
$random_sort = false;

// Enable sorting by resource ID?
$order_by_resource_id = false;

// Enable order by rating? Requires rating field updating to rating column.
$orderbyrating = false;

// Default sort order when viewing collection resources, options: 'date', 'colour', 'collection', 'popularity',
//  'country', and 'resourcetype'  When users are expecting resources to be shown in the order provided, set to
//  'collection'.
$default_collection_sort = 'collection';

// Enable sorting tabs alphabetically?
$sort_tabs = true;

// Enable automatically ordering checkbox lists alphabetically?
$auto_order_checkbox = true;

// Enable a case insensitive sort when automatically ordering checkbox lists alphabetically?
$auto_order_checkbox_case_insensitive = false;

// Enable ordering checkbox lists vertically, as opposed to horizontally, as HTML tables normally work?
$checkbox_ordered_vertically = true;

// Enable sorting by resource_type on thumbnail views?
$order_by_resource_type = true;

// Array of display fields to be added to the sort links in large, small, and extra large thumbnail views.
$sort_fields = array(12);

// For checkbox list searching, perform logical AND instead of OR when ticking multiple boxes?
$checkbox_and = false;

// For dynamic keyword list searching, perform logical AND instead of OR when selecting multiple options?
$dynamic_keyword_and = false;

// For dynamic keyword list suggestions, use logic 'contains' instead of 'starts with'?
$dynamic_keyword_suggest_contains = false;

// Uncomment to limit the suggestions to display after a certain number of characters have been entered. Useful if
//  dynamic keyword fields have a large number options. Set this to a value equal to your shortest dynamic keyword
//  option.
# $dynamic_keyword_suggest_contains_characters = 2;


// RESULTS DISPLAY SETTINGS
// Array of the number of results to display per page.
$results_display_array = array(24, 48, 72, 120, 240);

// Default number of results per page.
$default_perpage = 48;

// Array of the number of list results to display (User Admin, Public Collections, Manage Collections, etc.).
$list_display_array = array(15, 30, 60);

// Default number of list results per page.
$default_perpage_list = 15;

// Display an 'Edit' icon/link in the search results?
$search_results_edit_icon = true;

// Array of resource types which get the extra video icon in the search results.
$videotypes = array(3);

// Enable a small icon above thumbnails showing the resource type?
$resource_type_icons = false;

// Preview All default orientation, options: "v" = vertical or "h" = horizontal.
$preview_all_default_orientation = "h";

// Display the resource ID in the thumbnail, next to the action icons?
$display_resource_id_in_thumbnail = false;

// Highlight search keywords when displaying results and resources?
$highlightkeywords = true;

// Display the extension after the truncated text in the search results?
$show_extension_in_search = false;

// When returning to search results from the view page via "all" link, bring user to result location of viewed resource?
$search_anchors = true;

// Enable highlighting the last viewed result when using $search_anchors=true?
$search_anchors_highlight = false;

// EXPERIMENTAL: Always use 'download.php' to send thumbs and previews? Improved security as 'filestore' web access
//  can be disabled.
$thumbs_previews_via_download = false;

// Enable forcing fields with display templates to obey "order by" numbering?
$force_display_template_order_by = false;

// Enable playing audio and video files on hover, instead of a click on the Search page?
$video_search_play_hover = false;

// Use a FFmpeg alternative file for search preview playback?
$video_player_thumbs_view_alt = false;

// Video player alternative file name.
# $video_player_thumbs_view_alt_name = 'searchprev';


// SEARCH VIEWS SETTINGS
// Default display mode for search results, options: 'smallthumbs', 'thumbs', 'list'
$default_display = "thumbs";

// Enable replacement of text descriptions of search views (x-large, large, small, list) with icons?
$iconthumbs = true;

// Array of resource fields to display on the large thumbnail view.
$thumbs_display_fields = array(8);

// Array of defined $thumbs_display_fields to apply CSS modifications to via $search_results_title_wordwrap,
//  $search_results_title_height, $search_results_title_trim.
$thumbs_display_extended_fields = array();

// Result title height.
# $search_result_title_height = 26;

// Trim result title after X characters.
$search_results_title_trim = 30;

// Force breaking up of very large titles so they wrap to multiple lines, useful when using multiline titles with
//  $search_result_title_height. By default, this is set very high so that breaking does not occur. If you use titles
//  that have large unbroken words, e.g. filenames with no spaces, then useful to set a lower value, e.g. 20.
$search_results_title_wordwrap = 100;

// Display MP3 player in thumbnail view if $mp3_player=true?
$mp3_player_thumbs_view = false;

// Display FLV player in thumbnail view?
$video_player_thumbs_view = false;

// Enable extra large thumbnails option?
$xlthumbs = false;

// Array of resource fields to display in the extra large thumbnail view.
$xl_thumbs_display_fields = array(8);

// Array of defined $xl_thumbs_display_fields to apply CSS modifications to (via $xl_search_results_title_wordwrap,
//  $xl_search_results_title_height, $xl_search_results_title_trim).
$xl_thumbs_display_extended_fields = array();

// Extra large thumbnail result title height.
# $xl_search_result_title_height = 26;

// Trim extra large thumbnail title after X characters.
$xl_search_results_title_trim = 60;

// Wrap extra large thumbnail title after X characters.
$xl_search_results_title_wordwrap = 100;

// Display MP3 player in extra large thumbnail view if $mp3_player=true?
$mp3_player_xlarge_view = true;

// Display FLV player in extra large thumbnail view?
$flv_player_xlarge_view = false;

// Display embedded SWFs in extra large thumbnail view?
$display_swf_xlarge_view = false;

// Enable list view option?
$searchlist = true;

// Array of resource fields to display in the list view.
$list_display_fields = array(8, 3, 12);

// Trim list title result after X characters.
$list_search_results_title_trim = 25;

// Display the Resource ID column in the list view?
$id_column = true;

// Display the resource type column in the list view?
$resource_type_column = true;

// Display the resource archive status in the list view?
$list_view_status_column = false;


//----METADATA SETTINGS-------------------------------------------------------------------------------------------------
// Omit the option to extract metadata?
$metadata_read = true;

// If $metadata_read=true, is the default setting on the Edit and Upload pages to extract metadata?
$metadata_read_default = true;

// If $metadata_read=false, enable metadata import default.
$no_metadata_read_default = false;

// Show a link to re-extract metadata per-resource on the View Page to users who have edit abilities?
$allow_metadata_revert = false;

// Strip tags from rich fields when downloading metadata? Default is FALSE (keeping the tags added by CKEDITOR).
$strip_rich_field_tags = false;

// Enable a metadata report available on the View page?
$metadata_report = false;

// Number of characters from fields are 'mirrored' on to the resource table. Used for field displays in search results.
//  This is the varchar length of the 'field' columns on the resource table. Value can be increased if titles, etc.
//  are being truncated in search results, but the field column lengths must also be altered.
$resource_field_column_limit = 200;

// Filename text prefix to add.
$prefix_filename_string = "";

// Enable a resource ID prefix to the filename?
$prefix_resource_id_to_filename = false;

// Array of field references for fields that you do not wish the blank default entry to appear for, so the first
//  keyword node is selected by default. Example: array(3, 12);
$default_to_first_node_for_fields = array();

// Enable multilingual free text fields?  By default, only the checkbox list/dropdown fields can be multilingual by
//  using the special syntax when defining the options. However, setting TRUE below means that free text fields can
//  also be multilingual. Several text boxes appear when entering data, so that translations can be entered.
$multilingual_text_fields = false;


// METADATA RESOURCE FIELD SETTINGS
// When NOT using ExifTool, which field do we drop the EXIF data in to? Comment out to disable basic EXIF reading.
$exif_comment = 18; # Comment field.
$exif_model = 52; # Model field.
$exif_date = 12; # Date field.

// Original filename resource field ID.
$filename_field = 51;

// Resource default title field ID for all resources, used as title on the View and Collections pages, field will be
//  inherited even if 'Inherit Global Fields' is set to false.
$view_title_field = 8;

// Searchable Date resource ID field.
$date_field = 12;

// When extracting text from documents (e.g. HTML, DOC, TXT, PDF) field ID used for the actual content. Comment out
//  to prevent extraction of text content.
$extracted_text_field = 72;

// Resource field ID that will store 'Portrait' or 'Landscape' depending on image dimensions.
# $portrait_landscape_field = 1;

// Ability to generate an automated title using a specific format, such as a combination between the resource title,
//  its ID and file extension. Supported placeholders:
//    %title -> Replaced with the value of the title field of the resource.
//    %resource -> Replaced with the resource ID.
//    %extension -> Replaces the actual file extension.
// Examples:
//  $auto_generated_resource_title_format = '%title-%resource.%extension';
//  $auto_generated_resource_title_format = '2018-2019P - %resource.%extension';
//  $auto_generated_resource_title_format = 'Photos - %resource.%extension';
// Auto generated resource title format string.
$auto_generated_resource_title_format = '';

// To get the title as the filename on download, set:
// Automated download filename resource field ID.
$download_filename_field = 8; # Set this to the $view_title_field value.

// Array of file extensions to be removed from any metadata string at the point it is used in generating a download
//  filename.  Will not alter the stored metadata value, but provides an option to strip from it given file extensions.
//  Recommended that metadata containing file extensions is not used in a filename to avoid the administration of this
//  option.
# $download_filename_strip_extensions = array('jpg', 'jpeg', 'tif', 'png');


// METADATA TEMPLATE SETTINGS
// Enable Metadata Templates by setting to the resource type ID that you will use for metadata templates. Metadata
//  templates can be selected on the Resource Edit page to pre-fill fields. The intention is that you will create a
//  new resource type named "Metadata Template" and enter its ID below. This resource type can be hidden from view if
//  necessary, using the restrictive resource type permission. Metadata template resources act differently in that they
//  have editable fields for all resource types. This is so they can be used with any resource type, e.g. if you
//  complete the photo fields then these will be copied when using this template for a photo resource.
# $metadata_template_resource_type = 5;

// Set that a different field should be used for the 'title' in metadata templates, so that the original title field
//  can still be used for template data.
# $metadata_template_title_field = 10;

// Default metadata templates to a particular resource ID.
$metadata_template_default_option = 0;

// Enable forcing the selection of a metadata template?
$metadata_template_mandatory = false;


// EXIFTOOL SETTINGS (https://exiftool.org/)
// Use ExifTool to extract specified resolution and unit information from files (ex. Adobe files) upon upload?
$exiftool_resolution_calc = false;

// Enable writing metadata to files upon download if possible?
$exiftool_write = true;

// Enable forcing ExifTool to write metadata (no user option) to file at download if $exiftool_write=true?
$force_exiftool_write_metadata = false;

// Enable ExifTool writing metadata to file at resource and collection download if $exiftool_write=true?
$exiftool_write_option = false;

// Enable stripping out existing EXIF, IPTC, and XMP metadata when adding metadata to resources using ExifTool?
$exiftool_remove_existing = false;

// Omit conversion to UTF8 when ExifTool writes (happens when $mysql_charset is not set or $mysql_charset!="utf8")?
$exiftool_write_omit_utf8_conversion = false;

// Array of file extensions to NOT send to ExifTool for processing. Includes common video formats, as can cause
//  slowdowns when multiple downloads are in progress.
$exiftool_no_process = array('aaf', '3gp', 'asf', 'avchd', 'avi', 'cam', 'dat', 'dsh', 'flv', 'm1v', 'm2v', 'mkv', 'wrap', 'mov', 'mpeg', 'mpg', 'mpe', 'mp4', 'mxf', 'nsv', 'ogm', 'ogv', 'rm', 'ram', 'svi', 'smi', 'webm', 'wmv', 'divx', 'xvid', 'm4v');

// ExifTool global options that get applied to any ExifTool command run.
//  Example: $exiftool_global_options = "-x EXIF:CreateDate"; # Exclude EXIF:CreateDate tag.
$exiftool_global_options = "";


//----GEOLOCATION SETTINGS----------------------------------------------------------------------------------------------
// Disable geocoding features?
$disable_geocoding = false;

// Hide geolocation panel by default (a link to show it will be displayed instead)?
$hide_geolocation_panel = false;

// Display geographical search results in a modal?
$geo_search_modal_results = true;

// Enable geolocating multiple assets on a map that are part of a collection?
$geo_locate_collection = false;

// Map height in pixels on Resource View page.
$view_mapheight = 200;

// Array of upper/lower longitude/latitude bounds, defining areas that will be excluded from geographical search results
//  in the sequence: southwest lat, southwest long, northeast lat, northeast long
$geo_search_restrict = array(
    # array(50, -3, 54, 3)      # Example omission zone 1
    # ,array(-10, -20, -8, -18) # Example omission zone 2
    # ,array(1, 1, 2, 2)        # Example omission zone 3
    );


// OPENLAYERS SETTINGS
// Use Google Maps?  If so, must set an API key below.
$use_google_maps = false;

// Google Maps API key, see https://developers.google.com/maps/documentation/javascript/get-api-key.
# $google_maps_api_key = '';

// Default center and zoom for the map view when searching or selecting a new location, this is a world view.
//  USA example: $geolocation_default_bounds="-10494743.596017,4508852.6025659,4";
//  Utah example: $geolocation_default_bounds="-12328577.96607,4828961.5663655,6";
$geolocation_default_bounds = "-3.058839178216e-9,2690583.3951564,2";

// Array of OpenStreetMap tile servers.
$geo_tile_servers = array();
$geo_tile_servers[] = 'a.tile.openstreetmap.org';
$geo_tile_servers[] = 'b.tile.openstreetmap.org';
$geo_tile_servers[] = 'c.tile.openstreetmap.org';

// OpenLayers map layers to make available, first is the default. For Google layers: $geo_layers="osm, gmap, gsat, gphy";
$geo_layers = "osm";

// Enable caching of OpenStreetMap tiles on your server? This is slower when loading, but eliminates non-SSL content
//  warnings if your site is SSL, requires curl.
$geo_tile_caching = true;

// Tile cache lifetime, 1 year by default to prevent hitting tile server.
$geo_tile_cache_lifetime = 31536000; # 60*60*24*365

// Optional path to tile cache directory.
# $geo_tile_cache_directory = "";

// Add OpenLayers configuration options to this variable to overwrite all other options.
$geo_override_options = "";


//----IMAGE PROCESSING SETTINGS-----------------------------------------------------------------------------------------
// GENERAL PREVIEW SETTINGS
// Enable preventing previews from creating versions that result in the same size? If TRUE, PRE, THM, and COL sizes will not be considered.
$lean_preview_generation = false;

// Enable editing of internal sizes, will require additional updates to CSS settings?
$internal_preview_sizes_editable = false;

// Some image files can take a long time to preview or involve too many sofware dependencies (RAW). If so, enabling
//  these options allow ExifTool to attempt to extract a preview embedded in the file if the files were saved with
//  previews. If a preview image cannot be extracted from the file, ImageMagick will be used.
$photoshop_thumb_extract = false; # Adobe Photoshop file.
$cr2_thumb_extract = false; # Canon RAW file.
$nef_thumb_extract = false; # Nikon RAW file.
$dng_thumb_extract = false; # Adobe digital negative file.
$rw2_thumb_extract = true; # Panasonic RAW file.
$raf_thumb_extract = false; # Fuji RAW file.
$arw_thumb_extract = false; # Sony RAW file.

// Enable creation of a MIFF file for Photoshop EPS files? FALSE by default, as it is 4x slower than just ripping with
//  Ghostscript and bloats filestore.
$photoshop_eps_miff = false;


// COLOR PROFILE SETTINGS
// EXPERIMENTAL: ICC Color Management Features, ImageMagick must be installed and configured with LCMS support.
// Enable extraction and use of ICC profiles from original images?
$icc_extraction = false;

// Default ICC color profile used for all rendered files (just thumbnails if $imagemagick_preserve_profiles=true).
# $default_icc_file = 'my-profile.icc';

// Target color profile for preview generation.  The file must be located in the ../iccprofiles folder, target preview
//  will be used for the conversion, but will not be embedded.
$icc_preview_profile = 'sRGB_IEC61966-2-1_black_scaled.icc';

// Embed the target preview profile?
$icc_preview_profile_embed = false;

// Additional options for profile conversion during preview generation.
$icc_preview_options = '-intent perceptual -black-point-compensation';


// IMAGEMAGICK SETTINGS
// Enable preserving colour profiles for images above SCR?
$imagemagick_preserve_profiles = false;

// JPG preview image quality (0 = worst quality/lowest filesize, 100 = best quality/highest filesize).
$imagemagick_quality = 90;

// Preset preview quality settings (0-100) used by the Transform plugin to allow user to select from a range of preset
//  quality settings. If adding extra settings, an accompanying $lang setting must be set in a plugin language file or
//  using site text (Manage Content), Example: $lang['image_quality_10'] = "";
$image_quality_presets = array(100, 92, 80, 50, 40);

// Enable unique quality settings for each preview size? Will use $imagemagick_quality as a default. To adjust the
//  quality settings for internal previews, also set $internal_preview_sizes_editable=true.
$preview_quality_unique = false;

// Colorspace usage, options: "RGB" for ImageMagick <v6.7.6-4 and GraphicsMagick, "sRGB" for ImageMagick >=v6.7.6-4.
$imagemagick_colorspace = "RGB";

// Enable attempt to resolve height and width of the ImageMagick formats at view time? Enabling may cause a slowdown on
//  viewing resources when large files are used.
$imagemagick_calculate_sizes = false;

// EXPERIMENTAL: Enable ImageMagick Memory Program Register (MPR) optimizations? This will not work for GraphicsMagick.
$imagemagick_mpr = false;

// Set the depth to be passed to MPR command.
$imagemagick_mpr_depth = "8";

// Should colour profiles be preserved with MPR?
$imagemagick_mpr_preserve_profiles = true;

// If using MPR, array of metadata profiles to be retained. Default setting good for ensuring copyright info is not
//  stripped which may be required by law.
$imagemagick_mpr_preserve_metadata_profiles = array('iptc');


// GHOSTSCRIPT AND PDF SETTINGS
// Use the -dUseCIEColor command (generally TRUE, but added in some cases where scripts might not want it)?
$dUseCIEColor = true;

// Array of file extensions supported by Ghostscript.
$ghostscript_extensions = array('ps', 'pdf');


// IMAGE PREVIEW GENERATION SETTINGS
// Array of file extensions for which ResourceSpace will only generate the internal preview sizes.
$non_image_types = array();

// Array of preview sizes to always create. This is especially helpful if your preview size is smaller than THM size.
$always_make_previews = array();

// Enable creating all preview sizes at the full target size if image is smaller, except for HPR as this would result in massive images?
$previews_allow_enlarge = false;

// Enable generating only the internal preview sizes and show only the original file for download for any of the
// extensions found in a merge of $non_image_types, $ffmpeg_supported_extensions, $unoconv_extensions, and $ghostscript_extensions list?
$non_image_types_generate_preview_only = true;

// Array of file extensions that will not have previews automatically generated. This is to workaround a problem with
//  colour profiles, whereby an image file is produced, but is not a valid file format.
$no_preview_extensions = array("icm", "icc");

// Enable option to autorotate new images based on embedded camera orientation data? Requires ImageMagick.
$camera_autorotation = false;

// Enable default autorotation box checked.
$camera_autorotation_checked = true;

// Array of image formats to try autorotation.
$camera_autorotation_ext = array('jpg', 'jpeg', 'tif', 'tiff', 'png');

// Enable GraphicsMagick autorotation?
$camera_autorotation_gm = false;

// Enable applying image tweaks to images larger than SCR? If using Magictouch, may want tweaks like rotation to be
//  applied to the larger images as well. This could require recreating previews to sync up the various image rotations.
$tweak_all_images = false;

// Enable applying image tweaks to gamma adjustment?
$tweak_allow_gamma = true;

// Enable generating thumbnails/previews for alternative files?
$alternative_file_previews = true;

// Enable gnerating thumbnails/previews for alternative files in batch mode?
$alternative_file_previews_batch = true;

// Enable PSD file transparency checkerboard?
$psd_transparency_checkerboard = false;

// Checkerboard file with path for GIF and PNG files with transparency.
$transparency_background = "gfx/images/transparency.gif";

// Set the maximum size in MB of uploaded files that thumbnail/preview images will be created for. Useful when dealing
//  with very large files that may place a drain on system resources, for example 100MB+ Adobe Photoshop files will
//  take a great deal of CPU/memory for ImageMagick to process and it may be better to skip the automatic preview and
//  add a preview JPG manually using the "Upload a Preview Image" function on the Resource Edit page.
# $preview_generate_max_file_size = 100;


// IMAGE WATERMARK SETTINGS
// Path to watermark image to generate watermark images for 'internal' (thumbnail/preview) images if ImageMagick is
//  installed.. Groups with the 'w' permission will see these watermarks when access is 'restricted'. If set, you must
//  ensure watermarks are generated for all images, use ../pages/tools/update_previews.php?previewbased=true
//  NOTE: if set, restricted external emails will recieve watermarked versions and inherit the permissions of the
//  sender, but if watermarks are enabled, assume restricted access requires the equivalent of the "w" permission.
# $watermark = "gfx/watermark.png";

// Enable watermark thumbnail/preview for groups with the 'w' permission, even when access is 'Open'. Makes sense if
//  $terms_download=true.
$watermark_open = false;

// Enable extending $watermark_open to the search page if $watermark_open=true?
$watermark_open_search = false;

// Array of watermark posiitons to display watermark without repeating it, values: 'NorthWest', 'North', 'NorthEast',
//  'West', 'Center', 'East', 'SouthWest', 'South', and 'SouthEast'
$watermark_single_image = array(
    'scale'    => 40,
    'position' => 'Center',
    );


// PDF FILE SETTINGS
// If using ImageMagick for PDF, EPS, and PS files, how many pages should be extracted for the previews? If set to more
//  than 1, the user will be able to page through the PDF file.
$pdf_pages = 30;

// When uploading PDF files, split each page to a separate resource file?
$pdf_split_pages_to_resources = false;

// Enable replacing the preview of a PDF document with the PDFjs viewer if user has full access to the resource. This
//  allows the user to see the original file, having the ability to also search within the document. IMPORTANT: Enable
//  this per resource type as this will only work for PDF files to which user has full access.
$use_pdfjs_viewer = false;

// PDF/EPS base ripping quality in DPI. Higher values might greatly increase the resource usage on preview generation,
//  see $pdf_dynamic_rip on how to avoid.
$pdf_resolution = 150;

// Enable PDF/EPS dynamic ripping? Use pdfinfo (PDF) or identify (EPS) to extract document size in order to calculate
//  an efficient ripping resolution.  Useful mainly if you have odd sized PDFs, as you might in the printing industry;
//  Example: you have very large PDFs, such as 50 to 200 in (will greatly decrease ripping time and avoid overload) or
//  very small, such as PDFs < 5 in (will improve quality of the SCR image).
$pdf_dynamic_rip = false;


// ALTERNATIVE IMAGE SIZE AND FORMAT SETTINGS
// Array of alternative file types to enable support for storing an alternative type for each alternate file, first
//  value will be the default. Example: $alt_types = array("", "Print", "Web", "Online Store", "Detail");
$alt_types = array("");

// Generation of alternative image file sizes/formats using ImageMagick/GraphicMagick. It is possible to automatically
//  generate different file sizes and have them attached as alternative files and works in a similar way to video file
//  alternatives. The blocks must be numbered sequentially (0, 1, 2) and defined as:
//   'params' = Extra parameters to pass to ImageMagick, for example DPI.
//   'source_extensions' = Comma-separated list of the files that will be processed, e.g. "eps,png,gif" (note no spaces).
//   'source_params' = Parameters for the source file (e.g. -density 1200).

// Example: Automatically create a PNG file alternative when an EPS file is uploaded.
# $image_alternatives[0]["name"]              = "PNG File";
# $image_alternatives[0]["source_extensions"] = "eps";
# $image_alternatives[0]["source_params"]     = "";
# $image_alternatives[0]["filename"]          = "alternative_png";
# $image_alternatives[0]["target_extension"]  = "png";
# $image_alternatives[0]["params"]            = "-density 300"; # 300 dpi
# $image_alternatives[0]["icc"]               = false;

// Example: Automatically create a JPG file alternative when a JPG or TIF file is uploaded.
# $image_alternatives[1]["name"]              = "CMYK JPEG";
# $image_alternatives[1]["source_extensions"] = "jpg,tif";
# $image_alternatives[1]["source_params"]     = "";
# $image_alternatives[1]["filename"]          = "cmyk";
# $image_alternatives[1]["target_extension"]  = "jpg";
# $image_alternatives[1]["params"]            = "-quality 100 -flatten $icc_preview_options -profile ".dirname(__FILE__) . "/../iccprofiles/name_of_cmyk_profile.icc"; # Quality 100 JPG with specific CMYK ICC profile.
# $image_alternatives[1]["icc"]               = true; # Use source ICC profile in command.

// Example: Automatically create a JPG2000 file alternative when an TIF file is uploaded.
# $image_alternatives[2]['name']              = 'JPG2000 File';
# $image_alternatives[2]['source_extensions'] = 'tif';
# $image_alternatives[2]["source_params"]     = "";
# $image_alternatives[2]['filename']          = 'New JP2 Alternative';
# $image_alternatives[2]['target_extension']  = 'jp2';
# $image_alternatives[2]['params']            = '';
# $image_alternatives[2]['icc']               = false;

// QUICKLOOK PREVIEW SETTINGS
// Enable QuickLook previews to produce a preview for files using MacOS built-in QuickLook preview system that
//  supports multiple files by setting the path to QLpreview. Requires >=0.2 of 'qlpreview', from
//  http://www.hamsoftengineering.com/codeSharing/qlpreview/qlpreview.html.
# $qlpreview_path = "/usr/bin";

// Array of file extensions that QLPreview should NOT be used for.
$qlpreview_exclude_extensions = array("tif", "tiff");


//----VIDEO AND AUDIO PROCESSING SETTINGS-------------------------------------------------------------------------------
// Use VideoJS for video playback (as opposed to FlashPlayer, which we are deprecating)?
$videojs = true;

// Use qt-faststart to make MP4 previews start faster?
# $qtfaststart_path = "/usr/bin";
# $qtfaststart_extensions = array("mp4", "m4v", "mov");

// Video resolution selection: ability to use the original playback file and any files created via $ffmpeg_alternatives
//  for resolution selection options on the View page. Since $video_view_play_hover hides the control bar, its use will
//  override the use of resolution selection. "label" = the resolution identifier as it should appear in the selection
//  list and "name" = accepts names set in $ffmpeg_alternatives[]["name"]. For the main playback file, leave empty.
# $videojs_resolution_selection[0]["label"] = "HD";
# $videojs_resolution_selection[0]["name"] = "";

# $videojs_resolution_selection[1]["label"] = "SD";
# $videojs_resolution_selection[1]["name"] = "standard";


// Default resolution when using resolution selection. Must use the same label from preferred $ffmpeg_hls_streams entry
//  or value as one of the "label" settings from $videojs_resolution_selection. This will be ignored (i.e. set to
//  'Auto') if $video_preview_hls_support=true.
$videojs_resolution_selection_default_res = 'HD';

// Enable display of the current label in the control bar; otherwise, a gear icon is displayed?
$videojs_resolution_selection_dynamicLabel = false;


// FFMPEG SETTINGS
// Enable creation of a standard preview video for FFmpeg compatible files? A FLV (Flash Video) file will
//  automatically be produced for supported file types: AVI, MOV, MPEG, etc.
//  MP4 example: $ffmpeg_preview_options = '-f mp4 -ar 22050 -b 650k -ab 32k -ac 1';
$ffmpeg_preview = true;
$ffmpeg_preview_seconds = 120; # Seconds to preview.
$ffmpeg_preview_extension = "flv";
$ffmpeg_preview_min_width = 32;
$ffmpeg_preview_min_height = 18;
$ffmpeg_preview_max_width = 700;
$ffmpeg_preview_max_height = 394;
$ffmpeg_preview_options = "-f flv -ar 22050 -b:v 650k -ab 32k -ac 1 -strict -2";

// Array of file extensions that can be processed by FFmpeg, mostly video files.
$ffmpeg_supported_extensions = array('aaf', '3gp', 'asf', 'avchd', 'avi', 'cam', 'dat', 'dsh', 'flv', 'm1v', 'm2v',
    'mkv', 'wrap', 'mov', 'mpeg', 'mpg', 'mpe', 'mp4', 'mxf', 'nsv', 'ogm', 'ogv', 'rm', 'ram', 'svi', 'smi', 'webm',
    'wmv', 'divx', 'xvid', 'm4v');

// Ability to add a prefix to command when calling FFmpeg.
# $ffmpeg_command_prefix = "nice -n 10"; # Example for Linux using nice to avoid slowing down the server.

// If uploaded file is in the preview format already, should we transcode it anyway?  TRUE by default as of switching to
//  MP4 previews, because it is likely that uploaded MP4 files will need a lower bitrate preview and were not intended
//  to be the actual preview themselves.
$ffmpeg_preview_force = true;

// Enable playing the original file instead of the preview? Useful if users are on an internal network and want to see
//  HQ video.
$video_preview_original = false;

// Enable encoding previews asynchronous, if $php_path is set?
$ffmpeg_preview_async = false;

// Enable finding out and obeying the pixel aspect ratio (PAR)?
$ffmpeg_get_par = false;

// Use new qscale to maintain quality; otherwise, use -sameq?
$ffmpeg_use_qscale = true;

// Global options to be applied to every FFmpeg command.
//  Example for recent FFmpeg versions verbose output prevents completion: $ffmpeg_global_options = "-loglevel panic";
//  Example for older versions of FFmpeg as above: $ffmpeg_global_options = "-v panic";
$ffmpeg_global_options = "";

// Enable new snapshots when recreating FFMPEG previews? This is to aid in migration to MP4, when custom previews have
//  been uploaded.
$ffmpeg_no_new_snapshots = false;

// Enable video snapshots? Hovering over a search result thumbnail preview, will show the user frames from the video in
//  order for the user to get an idea of what the video is about. Set to 0 to disable.
$ffmpeg_snapshot_frames = 20;

// Number of seconds into the video at which snapshot should be taken, overrides $ffmpeg_snapshot_fraction. Only valid
//  if >=10 seconds.
# $ffmpeg_snapshot_seconds = 10;

// Specify a point in the video at which snapshot image is taken as a proportion of the video duration between 0 and 1.
# $ffmpeg_snapshot_fraction = 0.1;


// ALTERNATIVE VIDEO SIZES AND FORMATS
// It is possible to automatically generate different file sizes and have them attached as alternative files.  The
//  blocks must be numbered sequentially (0, 1, 2). Ensure the formats you are specifiying with vcodec and acodec are
//  supported by checking 'ffmpeg -formats'.
// "lines_min" = Minimum number of lines (vertical pixels/height) needed in the source file before the alternative video
//   file will be created. It prevents the creation of alternative files that are larger than the source when
//   alternative files are being used for creating downscaled copies, such as for web use.
//  Convert MOV to AVI example: "-g 60 -vcodec msmpeg4v2 -acodec pcm_u8 -f avi";

# $ffmpeg_alternatives[0]["name"] = "QuickTime H.264 WVGA";
# $ffmpeg_alternatives[0]["filename"] = "quicktime_h264";
# $ffmpeg_alternatives[0]["extension"] = "mov";
# $ffmpeg_alternatives[0]["params"] = "-vcodec h264 -s wvga -aspect 16:9 -b 2500k -deinterlace -ab 160k -acodec mp3 -ac 2";
# $ffmpeg_alternatives[0]["lines_min"] = 480;
# $ffmpeg_alternatives[0]["alt_type"] = 'mywebversion';

# $ffmpeg_alternatives[1]["name"] = "Larger FLV";
# $ffmpeg_alternatives[1]["filename"] = "flash";
# $ffmpeg_alternatives[1]["extension"] = "FLV";
# $ffmpeg_alternatives[1]["params"] = "-s wvga -aspect 16:9 -b 2500k -deinterlace -ab 160k -acodec mp3 -ac 2";
# $ffmpeg_alternatives[1]["lines_min"] = 480;
# $ffmpeg_alternatives[1]["alt_type"] = 'mywebversion';

// Enable storing FFmpeg alternative files with the previews, when using $originals_separate_storage=true?
$originals_separate_storage_ffmpegalts_as_previews = false;


// HTTP LIVE STREAMING (HLS) SETTINGS
// Enable HLS compatible previews separate sections with an m3u8 playlist to support adaptive bitrate streaming?
//  Requires $videojs=true, test if compatible with your target browsers before enabling, a sample video is at
//  https://videojs.github.io/videojs-contrib-hls/.  Options: 0 = no HLS support, 1 = create HLS previews, 2 = create
//  both HLS and standard preview for incompatible clients to use, but will take up more disk space.
$video_preview_hls_support = 0;
$video_preview_player_hls = 0;
$video_hls_preview_options = " -ar 22050 -ac 1 -hls_list_size 0 -hls_time 5 ";

// Configuration of HLS preview streams (variants) that will be generated.  See online advice on recommended variants
//  and these should be adjusted depending on your expected client capabilities.  Be aware when adding extra variants
//  that these will take up more disk space. Array keys:
//     'label'          How the stream will appear if the resolution switcher is enabled.
//     'id'             Unique code for the stream, keep lower case with only letters as per preview size codes.
//     'resolution'     Desired resolution as WIDTHxHEIGHT e.g. 320x180. Leave as '' to match resolution of resource.
//     'bitrate'        Desired video bitrate in kb/s of stream.
//     'audio_bitrate'  Desired audio bitrate in kb/s of stream.
// Low quality HLS example:
$video_hls_streams[0]["label"] = "Low";
$video_hls_streams[0]["id"] = "lo";
$video_hls_streams[0]["resolution"] = "320x180";
$video_hls_streams[0]["bitrate"] = "140";
$video_hls_streams[0]["audio_bitrate"] = "32";

// Standard definition HLS example:
$video_hls_streams[1]["label"] = "SD";
$video_hls_streams[1]["id"] = "sd";
$video_hls_streams[1]["resolution"] = "768x432";
$video_hls_streams[1]["bitrate"] = "1200";
$video_hls_streams[1]["audio_bitrate"] = "128";

// High-quality HLS example:
$video_hls_streams[2]["label"] = "HQ";
$video_hls_streams[2]["id"] = "hi";
$video_hls_streams[2]["resolution"] = "";
$video_hls_streams[2]["bitrate"] = "2000";
$video_hls_streams[2]["audio_bitrate"] = "256";


// AUDIO FILE SETTINGS
// Enable player for MP3 files? See http://flash-mp3-player.net/players/maxi/, will use VideoJS if $videojs=true;
$mp3_player = true;

// Array of file extensions which will be ported to MP3 format for preview. If an MP3 file is uploaded, the original MP3
//  file will be used for preview.
$ffmpeg_audio_extensions = array('wav', 'ogg', 'aif', 'aiff', 'au', 'cdda', 'm4a', 'wma', 'mp2', 'aac', 'ra', 'rm',
    'gsm');

// FFmpeg audio settings for MP3 previews, default to 64Kbps mono.
$ffmpeg_audio_params = "-acodec libmp3lame -ab 64k -ac 1";


//----FEATURED COLLECTIONS (THEMES) SETTINGS----------------------------------------------------------------------------
// Enable themes (promoted collections intended for showcasing selected resources)?
$enable_themes = true;

// Enable forcing Collections lists on the Themes page to be in Descending order?
$descthemesorder = false;

// Display a 'new' flag next to new themes (default themes created < 2 weeks ago)?
$flag_new_themes = true;

// If $flag_new_themes=true, the number of days to display new flag.
$flag_new_themes_age = 14;

// If a theme header contains a single collection, enable the title to be a direct link to the collection? Drilling down
//  is still possible via the >Expand tool, which replaces >Select when a deeper level exists.
$themes_single_collection_shortcut = false;

// Display only collections that have resources the current user can see?
$themes_with_resources_only = false;

// Enable column sorting in theme list view? Only works when $themes_category_split_pages=true.
$themes_column_sorting = false;

// Display date column with theme list view?
$themes_date_column = false;

// Display Resource ID column with theme list view?
$themes_ref_column = false;

// Display theme breadcrumbs?
$enable_theme_breadcrumbs = true;

// Display collection name below breadcrumbs?
$show_collection_name = false;

// Display results for public collections on numeric searches?
$search_public_collections_ref = true;


// THEME CATEGORY SETTINGS
// Number of theme category levels to show. If set to >1, a dropdown box will appear to allow browsing of sub-levels.
$theme_category_levels = 1;

// Enable theme direct jump mode?  If set, sub category levels DO NOT appear and must be directly linked to using custom
//  home panels or top navigation items or similar. $theme_category_levels must be >1.
$theme_direct_jump = false;

// Display theme categories as links and themes on separate pages?
$themes_category_split_pages = false;

// Display breadcrumb-style theme parent links instead of subcategories?
$themes_category_split_pages_parents = false;

// Enable including "Themes" root node before theme level crumbs to add context and link to the Themes page?
$themes_category_split_pages_parents_root_node = true;

// Enable navigation to deeper levels in theme category trees?  FALSE: link to matching resources directly.
$themes_category_navigate_levels = false;

// Display a count of themes and resources in theme category heading?
$show_theme_collection_stats = false;

// Enable theme names to be batch edited in the Themes page?
$enable_theme_category_edit = true;

// Display images along with theme category headers (image selected is the most popular within the theme category)?
$theme_images = true;

// If $theme_images=true, how many images to auto-select (if none, chosen manually)?
$theme_images_number = 1;

// Enable aligning theme images to the right on the Themes page?  Useful when there are multiple theme images.
$theme_images_align_right = false;


// THEMES SIMPLE VIEW SETTINGS
// Enable Themes simple view? Show featured collection categories and featured collections (themes) as basic tiles with
//  no images.  Can be tested or used for custom link by adding querystring parameter simpleview=true to themes.php
//  e.g. ../pages/themes.php?simpleview=true, only works with $themes_category_split_pages=true;
$themes_simple_view = false;

// Display images on themes and category tiles if $themes_simple_view=true?
$themes_simple_images = true;

// Enable a background image when $themes_simple_view=true?
$themes_show_background_image = false;

// Enable a single home slideshow image on Themes page if $themes_simple_view=true?
$featured_collection_static_bg = false;


// SMART THEMES SETTINGS
// Enable omitting archived resources from get_smart_themes, so if all resources are archived, the header will not show?
//  Generally, it is not possible to check for the existence of results based on permissions, but in the case of
//  archived files, an extra join can help narrow the smart theme results to active resources.
$smart_themes_omit_archived = false;


// CATEGORY TREE SETTINGS
// Should the category tree field, if one exists, default to being open instead of closed?
$category_tree_open = false;

// Should the category tree status window be shown?
$category_tree_show_status_window = true;

// Should searches using the category tree use AND for hierarchical keys?
$category_tree_search_use_and = false;

// Enable forcing single branch selection in category tree selection?
$cat_tree_singlebranch = false;

// Enable forcing selection of parent nodes when selecting a sub node?
$category_tree_add_parents = true;

// Enable forcing deselection of child nodes when deselecting a node?
$category_tree_remove_children = true;


// COLLECTION BAR SETTINGS
// Display the Collection Bar footer?
$collections_footer = true;

// Specify custom colours for background and foreground Collection Bar elements:
$collection_bar_background_override = '';
$collection_bar_foreground_override = '';

// Enable popout Collection Bar upon collection interaction, such as 'Select Collection'?
$collection_bar_popout = false;

// Hide the 'Remove Resources' link in the Collection Bar?
$remove_resources_link_on_collection_bar = true;

// Enable a 'Contact Sheet' link in the Collection Bar?
$contact_sheet_link_on_collection_bar = true;

// Hide the Collection Bar (hidden, not minimised) if it has no resources in it?
$collection_bar_hide_empty = false;

// Use the Chosen library for rendering dropdowns in the Collection Bar?
$chosen_dropdowns_collection = false;

// Number of options that must be present before including search capability for Collection Bar Chosen dropdowns.
$chosen_dropdowns_threshold_collection = 10;


// BROWSE BAR SETTINGS
// Enable the Browse Bar?
$browse_bar = true;

// Display workflow /archive states in Browse Bar?
$browse_bar_workflow = true;

// Default Browse Bar width.
$browse_default_width = 295;


// COLLECTION SETTINGS
// Enable adding a prefix to all collection refs, to distinguish them from resource refs?
$collection_prefix = "";

// Enable leaving collections in place when they have been published as themes instead of removeing from the user's My
//  Collections.
$themes_in_my_collections = false;

// Enable the 'Edit All' function in the collection and search actions dropdowns?
$show_edit_all_link = true;

// Ability to alter collection frame height and width:
$collection_frame_divider_height = 3;
$collection_frame_height = 153;

// Enable adding a collection link to email when user submits a collection of resources for review (upload stage only)?
//  This will send a collection containing only the newly uploaded resources.
$send_collection_to_admin = false;

// Enable internally sharing a collection which is not private?
$ignore_collection_access = false;

// Array to prevent resource types specified from being added to collections. Will not affect existing resources in
//  collections, example: $collection_block_restypes = array(3, 4);
$collection_block_restypes = array();

// Enable a preview page for entire collections for more side to side comparison ability, works with $collection_reorder_caption=true?
$preview_all = false;

// Enable minimizing collections frame when visiting preview_all.php?
$preview_all_hide_collections = true;

// Enable not displaying the link to toggle thumbnails in collection frame?
$disable_collection_toggle = false;

// In the collection frame, show or hide thumbnails by default? ("hide" is better if collections are not going to be
//  heavily used).
$thumbs_default = "show";

// Enable automatically showing thumbnails when you change collection, but only if $thumbs_default="show"?
$autoshow_thumbs = false;

// Number of thumbnails to show in the collections panel until a 'View All' link appears, linking to a search in the main window.
$max_collection_thumbs = 150;

// Display an 'Empty Collection' link which will empty the collection of resources, but not delete them?
$emptycollection = false;

// Link back to collections from log page, if "" then link is ignored.
//  Example: back_to_collections_link = "&lt;&lt;-- Back to My Collections &lt;&lt;--";
$back_to_collections_link = "";

// Enable adding user and access information to collection results in the collections panel dropdown? Extends the
//  width of the dropdown and is intended to be used with $collections_compact_style, but should also be compatible
//  with the traditional collections tools menu.
$collection_dropdown_user_access_mode = false;


// PUBLIC COLLECTION SETTINGS
// Enable Public Collections that are collections that have been set as public by users and are searchable at the bottom
//  of the Themes page.  If FALSE, it will still be possible for administrators to set collections as public as this
//  is how Themes are published.
$enable_public_collections = true;

// Enable hiding the owner in list of Public Collections?
$collection_public_hide_owner = true;

// Enable hiding the 'Access' column on the collection_public.php page?
$hide_access_column_public = false;

// Enable confining Public Collections display to the collections posted by the user's own group, sibling groups,
//  parent group and children groups, all collections can be accessed via a new 'View All' link.
$public_collections_confine_group = false;

// Should Public Collections exclude Themes?  Once a Public Collection has been given a Theme Category, should it be
//  removed from the Public Collections search results?
$public_collections_exclude_themes = true;


// COLLECTION MANAGEMENT SETTINGS
// Enable a selection box in the Collection Edit menu that allows you to select another accessible collection to base
//  the current one upon? Helpful if you would like to make variations on collections that are heavily commented upon
//  or reordered.
$enable_collection_copy = true;

// Enable hiding the 'Access' column on the Manage Collections page?
$hide_access_column = false;

// If $collections_compact_style=true, remove the 'Contact Sheet' link from the Manage Collections page?
$manage_collections_contact_sheet_link = true;

// Remove the 'Remove Resources' link from the Manage Collections page?
$manage_collections_remove_link = true;

// Remove the 'Share Resources' link from the Manage Collections page?
$manage_collections_share_link = true;

// Enable tool at the bottom of the Collection Manager list which allows users to delete any empty collections that they own?
$collections_delete_empty = false;

// Enable users capable of deleting a full collection of resources to do so from the Collection Manage page?
$collection_purge = false;

// Uncomment to set a point in time where collections are considered 'Active' and appear in the dropdown, based on
//  creation date. Older collections are effectively 'Archived', but accessible through Manage My Collections. Use any
//  English-language strings supported by the PHP strtotime() function.
# $active_collections = "-3 months";


// COLLECTION SHARING SETTINGS
// Enable empty collections to be shared?
$collection_allow_empty_share = false;

// Enable collections containing resources that are not active to be shared?
$collection_allow_not_approved_share = false;

// Enable creating a collection when sharing an individual resource via email?
$share_resource_as_collection = false;

// Enable hiding display of internal URLs when sharing collections? Intended to prevent inadvertently sending external
//  users invalid URLs.
$hide_internal_sharing_url = false;

// Enable multiple collections to be emailed at once?
$email_multi_collections = false;


// SMART COLLECTION SETTINGS
// Enable saving searches as Smart Collections which self-update based on a saved search?
$allow_smart_collections = false;

// Enable running Smart Collections asynchronously (faster smart collection searches, with the tradeoff that they are
//  updated after the search)?  May not be appropriate for usergroups that depend on live updates in workflows based
//  on Smart Collections.
$smart_collections_async = false;


// COLLECTION FEEDBACK SETTINGS
// Enable requiring an email address to be entered when users are submitting collecion feedback?
$feedback_email_required = true;

// When requesting feedback, allow the user to select resources (e.g. pick preferred photos from a photo shoot)?
$feedback_resource_select = false;

// When requesting feedback, display the contents of the specified field, if available, instead of the resource ID.
# $collection_feedback_display_field = 51;


//----UPLOAD SETTINGS---------------------------------------------------------------------------------------------------
// Enable users to skip upload and create resources with no attached file?
$upload_no_file = false;

// Display required fields legend on upload?
$show_required_field_label = true;

// Enable new upload, edit, and approve resources mode moving them to the correct stage?
$upload_then_edit = false;

// Enable new upload mode that focuses on getting files into the filestore, then working off a queue for further
//  processing (metadata extract, preview creation, etc)?  Requires $offline_job_queue=true;
$upload_then_process = false;

// Enable reviewing resources based on resource ID (starting from most recent) when using upload then edit mode?
//  Requires $upload_then_edit = true;
$upload_review_mode_review_by_resourceid = true;

// If $upload_then_process=true, set archive state where files are stored before processing.  Strongly recommended
//  that a unique archive state be created to handle this.
# $upload_then_process_holding_state = -3;

// If $upload_then_process=true and $upload_then_process_holding_state is set, add archive state title text.
# $lang['status-3']="Pending upload processing";

// Display upload options at the top of Edit page (Collection, Import Metadata checkboxes), rather than the bottom?
$edit_upload_options_at_top = false;

// Enable upload log display in the browser on the Upload page (not stored or saved)?
$show_upload_log = true;

// Enable group-based upload folders with separate local upload folders for each group?
$groupuploadfolders = false;

// Enable username based upload folders with separate local upload folders for each user based on username?
$useruploadfolders = false;

// Display the 'Clear' button on the Upload page?
$clearbutton_on_upload = true;

// Enable option to the upload page that allows resources uploaded together to all be related?  Requires
//  $enable_related_resources=true and $php_path be set.
$relate_on_upload = false;

// Enable making relating all resources at upload the default option if $relate_on_upload=true?
$relate_on_upload_default = false;

// Array of file extentions that cannot be uploaded for security reasons.  Example: uploading a PHP file may allow
//  arbirtary execution of code, depending on server security settings.
$banned_extensions = array("php", "cgi", "pl", "exe", "asp", "jsp", 'sh', 'bash');

// Option to change the location of the upload folder, so that it is not in the web visible path. Relative and
//  absolute paths are allowed.
$local_ftp_upload_folder = 'upload/';

// Use a file tree display for local folder upload?
$local_upload_file_tree = false;

// Enable hiding links to other uploaders?
$hide_uploadertryother = false;

// Option to selectively disable upload methods.  Options:
//  'single_upload' = enable or disable "Add Single Resource"
//  'in_browser_upload' = enable or disable "Add Resource Batch - In Browser"
//  'fetch_from_ftp' = enable or disable "Add Resource Batch - Fetch from FTP server"
//  'fetch_from_local_folder' = enable or disable "Add Resource Batch - Fetch from local upload folder"
$upload_methods = array(
    'single_upload' => true,
    'in_browser_upload' => true,
    'fetch_from_ftp' => true,
    'fetch_from_local_folder' => true,
    );

// Disable thumbnail generation during batch resource upload from FTP or local folder? This also works for normal
//  uploads (through web browser), setting may be overridden if previews are required at upload time e.g. if Google
//  Vision facial recognition is configured with a dependent field. A multi-threaded thumbnail generation script is
//  available: ../batch/create_previews.php, used as as a cron job or manually.
$enable_thumbnail_creation_on_upload = true;

// Enable allowing users to see all resources that they uploaded, irrespective of 'z' permissions?
$uploader_view_override = true;

// Display tabs on the Edit/Upload page? Disables collapsible sections.
$tabs_on_edit = false;

// Display a popup to users that upload resources are in the Pending Submission status? Prompts user to either submit
//  for review or continue editing.
$pending_submission_prompt_review = true;

// Display a 'User Contributed Assets' link on the My Contributions page? Allows non-admin users to see the assets
//  they have contributed.
$show_user_contributed_resources = true;


// UPLOAD METADATA OPTION SETTINGS
// Enable forcing users to select a resource type at upload?
$resource_type_force_selection = false;

// Enable users to lock metadata fields when in $upload_then_edit=true mode?
$upload_review_lock_metadata = true;

// Enable users to select to import or append embedded metadata on a field-by-field basis?
$embedded_data_user_select = false;

// Always display the option to override the import or appending/prepending of embedded metadata for the fields specified in the array.
# $embedded_data_user_select_fields = array(1, 8);

// Enable when uploading to include a user selectable option to use the embedded filename to generate the title?
$merge_filename_with_title = false;

// Set embedded filename with title default, options: 'do_not_use', 'replace', 'prefix', or 'suffix'.
$merge_filename_with_title_default = 'do_not_use';

// Enable default date left blank, instead of current date?
$blank_date_upload_template = false;

// Enable storing Resource Refs when uploading?  Useful for other developer tools to hook into the upload.
$store_uploadedrefs = false;

// Enable recording the name of the resource creator for new records?  If FALSE, will only record when a resource is
//  submitted into a provisional status.
$always_record_resource_creator = true;

// Enable importing the contents of a ZIP file to a text field on upload by setting the resource field ID, requires
//  'unzip' on the command path. If $zip_contents_field is not set, but 'unzip' is available, the archive contents
//  will be written to $extracted_text_field.
# $zip_contents_field = 18;

// Number of lines to remove from the top of the ZIP contents output in order to remove the filename field and other
//  unwanted header information.
$zip_contents_field_crop = 1;


// UPLOAD STATUS AND ACCESS SETTINGS
// Enable override default access value for the upload page. This will override the default resource template value by
//  changing the value of this option to the access ID number.
$override_access_default = false;

// Enable override default status value for the upload page. This will override the default resource template value by
//  changing the value of this option to the status ID number.
$override_status_default = false;

// Enable the 'Status' and 'Access' fields in the upload metadata template?
$show_status_and_access_on_upload = false;

// Permission required to show 'Access' and 'Status' fields on upload, evaluates PHP code so must be preceded with
//  'return' and end with a semicolon, FALSE = No permission required, can stack permissions:
//  " return !checkperm('e0') && !checkperm('c')";
$show_status_and_access_on_upload_perm = "return !checkperm('F*');";

// Enable showing 'Access' on upload? Acts as an override for the 'Status' and 'Access' flag.
//  Default: $show_status_and_access_on_upload;  Add unset($show_access_on_upload); to config if you wish to honour
//  TRUE/FALSE or FALSE/TRUE variations.
# Show Status and Access = true && Show Access = true     # 'Status' and 'Access' shown.
# Show Status and Access = false && Show Access = true    # Only 'Access' shown.
# Show Status and Access = true && Show Access = false    # Only 'Status' shown.
# Show Status and Access = false && Show Access = false   # Neither shown.
$show_access_on_upload = &$show_status_and_access_on_upload;

// Permission required to show 'Access' field on upload, this evaluates PHP code so must be preceded with 'return'.
//  TRUE = No permission required. Example ensures they have permissions to edit active resources:
//  $show_access_on_upload_perm = "return checkperm('e0')"; Stack permissions= "return checkperm('e0') && checkperm('c');";
$show_access_on_upload_perm = "return true;";


// PLUPLOAD SETTINGS
// Maximum upload file size; directly translates into PLupload's max_file_size if set.
# $plupload_max_file_size = '50M';

// PLupload chunk size, set to '' to disable chunking, may resolve issues with the Flash uploader.
$plupload_chunk_size = '5mb';

// Use the JQuery UI Widget instead of the Queue interface that includes a stop button and optional thumbnail mode?
$plupload_widget = true;

// Display resource thumbnails in the PLupload UI widget?
$plupload_widget_thumbnails = true;

// PLupload supported runtimes and priority.
$plupload_runtimes = 'html5,gears,silverlight,browserplus,flash,html4';

// Enable starting uploads as soon as files are added to the queue?
$plupload_autostart = false;

// Enable clearing the queue after uploads have completed?
$plupload_clearqueue = true;

// Enable keeping failed uploads in the queue after uploads have completed?
$plupload_show_failed = true;

// Maximum number of attempts to upload a file chunk before erroring.
$plupload_max_retries = 5;

// Enable upload multiple times the same file in a row? Set to TRUE only if you want RS to create duplicates when a
//  client is losing connection with the server and tries again to send the last chunk.
$plupload_allow_duplicates_in_a_row = false;

// Suffix used to identify alternatives for a particular resource when both the original file and its alternatives
//  are being uploaded in a batch using the UI (PLupload).  IMPORTANT: This will only work if the user uploads all
//  files (resource and its alternatives) into the same collection.
$upload_alternatives_suffix = '';


// UPLOAD CHECKSUM SETTINGS
// Enable creating file checksums on upload?
$file_checksums = false;

// Enable calculating checksums on first 50k and size?  FALSE: checksums on the full file.
$file_checksums_50k = true;

// Enable blocking duplicate files based on checksums (has performance impact)? May not work reliably with
//  $file_checksums_offline=true, unless checksum script is run frequently.
$file_upload_block_duplicates = false;

// Enable generating checksums with a background cron job? Recommended if files are large, since checksums can take time.
$file_checksums_offline = true;


// BATCH UPLOAD SETTINGS
// Default resource type ID to use for batch upload templates.
$default_resource_type = 1;

// When batch uploading, display the 'Add Resources to Collection' selection box?
$enable_add_collection_on_upload = true;

// When batch uploading, enable users to set collection public as part of upload process? Also allows theme assignment
//  for users who have appropriate privileges.
$enable_public_collection_on_upload = false;

// Enable batch uploads "Add to New Collection" option?
$upload_add_to_new_collection_opt=true;

// Enable batch uploads "Add to New Collection" default. Set to FALSE for "Do not Add to Collection".
$upload_add_to_new_collection = true;

// Enable batch uploads "Do Not Add to New Collection" option? Set to FALSE to force upload to a collection.
$upload_do_not_add_to_new_collection_opt = true;

// Enable "Do Not Add to a Collection" as the default option for upload?
$do_not_add_to_new_collection_default = false;

// Enable batch uploads requiring that a collection name is entered, to override the Upload<timestamp> default behavior?
$upload_collection_name_required = false;

// Enable batch uploads to always upload to Default Collection?
$upload_force_mycollection = false;

// Enable hiding hidden collections on batch uploads?
$hidden_collections_hide_on_upload = false;

// Enable show/hide hidden collection toggle on batch uploads? Must set $hidden_collections_hide_on_upload=true;
$hidden_collections_upload_toggle = false;

// Enable the batch upload 'Copy Resource Data from Existing Resource' feature?
$enable_copy_data_from = true;

// When batch uploading and editing the template, should the date be reset to today's date?  If FALSE, the previously
//  entered date is used.  Note that if $upload_then_edit=true, then this will happen at the upload stage in order to
//  get the similar behaviour for this mode.
$reset_date_upload_template = true;

// Date field ID to reset if using multiple date fields.
$reset_date_field = 12;

// When batch uploading and editing the template, should all values be reset to blank or the default value every time?
$blank_edit_template = false;

// Batch replace local folder.
$batch_replace_local_folder = "/upload";


//----DOWNLOAD SETTINGS-------------------------------------------------------------------------------------------------
// Enable download of original file for resources with "Restricted" access? For the tailor made preview sizes and
//  downloads, this value is set per preview size in the system setup.
$restricted_full_download = false;

// Enable download of collections as archives (ZIP or TAR)?  If TRUE, overrides depredicated $zipcommand.  Also
//  $collection_download_settings and $archiver_path, etc. above.
$collection_download = false;

// Example for Linux with the zip utility:
# $collection_download_settings[0]["name"] = 'ZIP';
# $collection_download_settings[0]["extension"] = 'zip';
# $collection_download_settings[0]["arguments"] = '-j';
# $collection_download_settings[0]["mime"] = 'application/zip';

// Example for Linux with the 7z utility:
# $collection_download_settings[0]["name"] = 'ZIP';
# $collection_download_settings[0]["extension"] = 'zip';
# $collection_download_settings[0]["arguments"] = 'a -tzip';
# $collection_download_settings[0]["mime"] = 'application/zip';
# $collection_download_settings[1]["name"] = '7Z';
# $collection_download_settings[1]["extension"] = '7z';
# $collection_download_settings[1]["arguments"] = 'a -t7z';
# $collection_download_settings[1]["mime"] = 'application/x-7z-compressed';

// Example for Linux with tar (saves time if large compressed resources):
# $collection_download_settings[0]["name"] = 'tar file';
# $collection_download_settings[0]["extension"] = 'tar';
# $collection_download_settings[0]["arguments"] = '-cf ';
# $collection_download_settings[0]["mime"] = 'application/tar';
# $archiver_path = '/bin';
# $archiver_executable = 'tar';
# $archiver_listfile_argument = " -T ";

// Example for Windows with the 7z utility:
# $collection_download_settings[0]["name"] = 'ZIP';
# $collection_download_settings[0]["extension"] = 'zip';
# $collection_download_settings[0]["arguments"] = 'a -scsWIN -tzip';
# $collection_download_settings[0]["mime"] = 'application/zip';

// Maximum size in bytes of the collection download prior to ZIP/TAR. Prevents users attempting very large downloads.
$collection_download_max_size = 1073741824; # Default 1GB = 1024*1024*1024.

// Iframe-based direct download from the View page (to avoid going to download.php), incompatible with $terms_download
//  and the $download_usage features, and is overridden by $save_as.
// Display the download iframe for debugging purposes?
$debug_direct_download = false;

// Enable making downloaded filename to be just <resource id>.extension, without indicating size or whether an
//  alternative file. Will override $original_filenames_when_downloading that is set as default.
$download_filename_id_only = false;

// Enable appending the size to the filename when downloading, requires $download_filename_id_only=true;
$download_id_only_with_size = false;

// Use English/Imperial instead of metric for the download size guidelines?
$imperial_measurements = false;

// Download_chunk_size for resource downloads.  Try changing to 4096 if experiencing slow downloads.
$download_chunk_size = (2 << 20);

// Use original filename when downloading a file?
$original_filenames_when_downloading = true;

// Should the download filename have the size appended to it?
$download_filenames_without_size = false;

// If $original_filenames_when_downloading=true, should the original filename be prefixed with the resource ID?  This
//  ensures unique filenames when downloading multiple files.  WARNING: If FALSE, when downloading a collection as a
//  ZIP file, a file with the same name as another file in the collection will overwrite that existing file. It is
//  therefore best to leave as TRUE.
$prefix_resource_id_to_filename = true;

// If $prefix_resource_id_to_filename=true, the string prefix before the resource ID to add. Useful to establish that
//  a resource was downloaded from ResourceSpace and that the following number is a ResourceSpace resource ID.
$prefix_filename_string = "RS";

// Display the download as a 'Save As' link, instead of redirecting the browser to the download (which sometimes
//  causes a security warning)? For Opera and IE7 browsers, this will always be enabled regardless of this setting as
//  these browsers block automatic downloads by default.
$save_as = false;

// Should the automatically produced FLV video file be available as a separate download?
$flv_preview_downloadable = false;

// Option to block download (hide the button) if user selects specific option(s). Only used as a guide for the user
//  e.g. to indicate that permission should be sought.
# $download_usage_prevent_options = array("Press");

// Option to change the FFmpeg download name from the default "FLV File" to a custom string.
# $ffmpeg_preview_download_name = "Flash web preview";

// Option to change the original download filename (Use %EXTENSION, %extension or %Extension as a placeholder. Using ?
//  is now deprecated. The placeholder will be replaced with the filename extension, using the same case. E.g.
//  "Original %EXTENSION file" -> "Original WMV file")
# $original_download_name = "Original %EXTENSION file";

// Enable preventing client side users to get access to the real path of the resource when ResourceSpace is using
//  filestore URLs? Rather than "http://yourdomain/filestore/1_6326bb8314c6c21/1pre_cf33a61f47b5982.jpg", it will use
//  the download.php page to give back the file. This prevents users from coming back and download the files after their
//  permissions to the assets have been revoked.
$hide_real_filepath = false;

// Use the collection name in the downloaded ZIP filename when downloading collections as a ZIP file?
$use_collection_name_in_zip_name = false;

// Enable metadata field that will be used for downloaded filename, do not include file extension.
# $download_filename_field = 8;

// Enable direct resource downloads without authentication? WARNING: allows anyone to download previews without
//  logging in.
$direct_download_noauth = false;

// Enable preview direct links going directly to the filestore rather than through download.php?  Filestore must be
//  served through the web server for this to work. WARNING: allows anyone to download previews without logging in.
$direct_link_previews_filestore = false;


// DOWNLOAD BROWSER SETTINGS
// MIME types by file extension, used by ../pages/download.php to detect the MIME type of the file proposed to download.
$mime_type_by_extension = array(
    'mov'  => 'video/quicktime',
    '3gp'  => 'video/3gpp',
    'mpg'  => 'video/mpeg',
    'mp4'  => 'video/mp4',
    'avi'  => 'video/msvideo',
    'mp3'  => 'audio/mpeg',
    'wav'  => 'audio/x-wav',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'png'  => 'image/png',
    'odt'  => 'application/vnd.oasis.opendocument.text',
    'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
    'odp'  => 'application/vnd.oasis.opendocument.presentation',
    'svg'  => 'image/svg+xml',
    'pdf'  => 'application/pdf',
  );

// IE7 blocks initial downloads but after allowing once, it seems to work, so this option is available, no guarantees.
$direct_download_allow_ie7 = false;

// IE7 blocks initial downloads but after allowing once, it seems to work, so this option is available, no guarantees.
$direct_download_allow_ie8 = false;

// Opera can also allow popups, but is recommended FALSE as well since by default it will not work for most users.
$direct_download_allow_opera = false;

// Set to TRUE to prevent possible issues with IE and download.php. Found an issue with a stray pragma: no-cache
//  header that seemed to be added by SAML SSO solution.
$download_no_session_cache_limiter = false;


// DOWNLOAD PAGE SETTINGS
// Enable replacing the collection actions dropdown with a simple 'Download' link if $collection_download=true?
$collection_download_only = false;

// Enable requiring terms for download?
$terms_download = false;

// Enable asking the user the intended usage when downloading?
$download_usage = false;

// If $download_usage=true, the usage options available to the user.
$download_usage_options = array("Press", "Print", "Web", "TV", "Other");

// Enable removing the textbox on the Download Usage page?
$remove_usage_textbox = false;

// Enable moving the textbox below the dropdown on the Download Usage page?
$usage_textbox_below = false;

// Enable make filling in usage text box a non-requirement?
$usage_comment_blank = false;

// Enable hiding the download size for "Restricted" resources if the user does not have the ability to request ("q" permission)?
$hide_restricted_download_sizes = false;


// METADATA DOWNLOAD SETTINGS
// Enable writing a text file into zipped collections containing resource data?
$zipped_collection_textfile = false;

// Enable default option for text file download to FALSE.
$zipped_collection_textfile_default_no = false;

// Enable metadata download in the View page?
$metadata_download = false;

// If $metadata_download=true, the page header title to use.
$metadata_download_header_title = 'ResourceSpace';

// Custom logo to use when downloading metadata in PDF format.
# $metadata_download_pdf_logo = '/path/to/logo/location/logo.png';

// If $metadata_download=true, the page footer text to use.
$metadata_download_footer_text = '';

// Enable adding original URL column to CSV download?
$csv_export_add_original_size_url_column = false;


//---RESOURCE SETTINGS--------------------------------------------------------------------------------------------------
// Use checkboxes for selecting resources?
$use_checkboxes_for_selection = false;

// Array to map the resource type to a Font Awesome 4 icon.
$resource_type_icons_mapping = array(1 => "camera", 2 => "file", 3 => "video-camera", 4 => "music");

// Option to add workflow states to the default list of -2 (pending submission), -1 (Pending review), 0 (Active), 1
//  (Awaiting archive), 2 (archived), and 3 (deleted). Can be used in conjunction with 'z' permissions to restrict
//  access to workflow states. Example: $additional_archive_states = array(4, 5);
$additional_archive_states = array();

// For any new state, you need to create a corresponding language entry set to an appropriate description.
# $lang['status4'] = "Pending media team review";
# $lang['status5'] = "Embargoed";

// Should alternative files be visible to restricted users; however, they must still request access to download?
$alt_files_visible_when_restricted = true;

// Display expiry warning when expiry date has been passed?
$show_expiry_warning = true;


// RESOURCE TYPE SETTINGS
// Default mapping between resource types and file extensions. Can be used to automatically create resources in the
//  system based on the extension of the file.
$resource_type_extension_mapping_default = 1;
$resource_type_extension_mapping = array(
    2 => array('pdf', 'doc', 'docx', 'epub', 'ppt', 'pptx', 'odt', 'ods', 'tpl', 'ott' , 'rtf' , 'txt' , 'xml'),
    3 => array('mov', '3gp', 'avi', 'mpg', 'mp4', 'flv', 'wmv'),
    4 => array('flac', 'mp3', '3ga', 'cda', 'rec', 'aa', 'au', 'mp4a', 'wav', 'aac', 'ogg'),
    );

// Array of resource type IDs that cannot upload files. They are only being used to store information. By default, the
//  preview will default to "No preview" icon. In order to get a resource type specific one, make sure you add it to
//  gfx/no_preview/resource_type/, intended use is with $pdf_resource_type_templates.
$data_only_resource_types = array();

// Array of resource type templates and stored in /filestore/system/pdf_templates.  A resource type can have more than
//  one template. When generating PDFs, if there is no request for a specific template, the first one will be used so
//  make sure the the most generic template is the first one. You also cannot have an empty array of templates for a
//  resource type. IMPORTANT: you cannot use <html>, <head>, <body> tags in these templates as they are supposed to
//  work with the HTML2PDF library.  For more information, see http://html2pdf.fr/en/default.
//  Example:
# $pdf_resource_type_templates = array(
#    2 => array('case_studies', 'admins_case_studies')
#    );
$pdf_resource_type_templates = array();

// Array of resource types IDs to visually hide when searching and uploading.  These types will still be available,
//  subject to filtering.
$hide_resource_types = array();


// RESOURCE VIEW SETTINGS
// Enable image preview zoom using jQuery.zoom, hover over the preview image to zoom in on the Resource View page?
$image_preview_zoom = false;

// Default DPI setting for the Resource View page if no resolution is stored in the database for the image.
$view_default_dpi = 300;

// Display the Resource ID on the Resource View page?
$show_resourceid = true;

// Display the resource type on the Resource View page?
$show_resource_type = false;

// Display the 'Access' on the Resource View page?
$show_access_field = true;

// Display the 'Contributed By' field on the Resource View page?
$show_contributed_by = true;

// Display 'Related Themes and Public Collections' panel on the Resource View page?
$show_related_themes = true;

// Display a list of collections that a resource belongs to on the Resource View page?
$view_resource_collections = false;

// Size of the related resource previews on the Resource View page. Usually requires some restyling
//  (#RelatedResources .CollectionPanelShell), takes the preview code such as "col", "thm".
$related_resource_preview_size = "col";

// Enable separating related resource results into separate sections (ie. PDF, JPG)?
$sort_relations_by_filetype = false;

// Enable separating related resource results into separate sections by resource type (ie. document, photo)?
$sort_relations_by_restype = false;

// Display a Download Summary on the Resource View page.
$download_summary = false;

// Display SWF files in full on the Resource View page?  Set $dump_gnash_path and JPG previews are not created yet.
$display_swf = false;

// Enable direct link to original file for each image size?
$direct_link_previews = false;

// Display a specified metadata field below the resource preview image on the view page, useful for photo captions.
# $display_field_below_preview = 18;

// Enable display fields with display templates in their ordered position instead of at the end of the metadata on the
//  Resource View page?
$force_display_template_orderby = false;

// Display the Resource View in a modal when accessed from search results?
$resource_view_modal = true;

// Use the preview size on the Resource View page?
$resource_view_use_pre = false;

// Array of file extensions to only use use the larger layout on the Resource View page.
# $resource_view_large_ext = array("jpg", "jpeg", "tif", "tiff", "gif", "png", "svg");

// Display the header and footer on resource preview page?
$preview_header_footer = false;

// Display a link that allows a user to email the $email_notify address about the resource to the Resource View page?
$resource_contact_link = false;

// Option to show related resources of specified resource types in a table alongside resource data. These resource types will not then be shown in the usual related resources area.
# $related_type_show_with_data = array(3, 4);

# Option to show the specified resource types as thumbnails if in $related_type_show_with_data array
# $related_type_thumbnail_view = array(3);

// Enable a link for those with edit access allowing upload of new related resources? The resource type will then be automatically selected for the upload.
$related_type_upload_link = true;

// Enable a thumbnail mouseover for alternative files to see the preview image?
$alternative_file_previews_mouseover = false;

// Related Resource title trim, set to 0 to disable.
$related_resources_title_trim = 15;

// Enable hiding the 'Share' link on the Resource View page?
$hide_resource_share_link = false;

// Enable playing audio and video files on hover, instead of a click on the Resource View page?
$video_view_play_hover = false;

// Enable playuing audio and video files on hover, instad of a click on the Preview and Preview All pages?
$video_preview_play_hover = false;

// Enable organization of the View page according to alt_type?
$alt_types_organize = false;


// RESOURCE EDITING SETTINGS
// Display a larger preview image on the Edit page?
$edit_large_preview = true;

// Display the Resource Edit in a modal when accessed from Resource View modal.
$resource_edit_modal_from_view_modal = false;

// Enable automatically saving the edits after making changes?
$edit_autosave = true;

// Enable option to use CTRL + S on the Edit page to save data?
$ctrls_to_save = false;

// Display a 'Clear' button on the Edit page?
$clearbutton_on_edit = true;

// Display 'Save' and 'Clear' buttons at the top of the Edit page as well as at the bottom?
$edit_show_save_clear_buttons_at_top = false;

// Enable the 'Related Resources' field when editing resources?
$enable_related_resources = true;

// Disable link to 'Upload Preview' on the Edit page?
$disable_upload_preview = false;

// Disable link to 'Manage Alternative Files' on the Edit page?
$disable_alternative_files = false;

// If $show_resource_title_in_titlebar=true, display 'Upload Resources' or 'Edit Resource' when on the Edit page.
$distinguish_uploads_from_edits = false;

// Display link to request log on the Resource View page?
$display_request_log_link = false;

// Enable keeping original resource files as alternatives when replacing resource?
$replace_resource_preserve_option = false;

// Enable $replace_resource_preserve_option to be checked by default?
$replace_resource_preserve_default = false;

// Enable replacement of multiple resources by filename using the "Replace Resource Batch" functionality?
$replace_batch_existing = false;

// Display and allow to remove custom access for users when editing a resource?
$delete_resource_custom_access = false;

// Enable administrators to change the value of the 'Contributed By' user for a resource?
$edit_contributed_by = false;

// Display a COL size image of resource on the Alternative File Management page?
$alternative_file_resource_preview = true;

// Display the resource title on the Alternative File Management page?
$alternative_file_resource_title = true;

// Display a COL size image of resource on the Replace File page?
$replace_file_resource_preview = true;

// Display the resource title on the Replace File page?
$replace_file_resource_title = true;

// Enable permission to show the replace file, preview image only, and alternative file options on the Resource Edit
//  page? Overrides required permission of F*
$custompermshowfile = false;


// RESOURCE DELETION SETTINGS
// Enable users to delete resources? Can be controlled on a more granular level with the "D" restrictive permission.
$allow_resource_deletion = true;

// When resources are deleted, the state they are moved to. Can be set to move into an alternative state instead of
//  removing the resource and its files from the system entirely. The resource will still be removed from any
//  collections it has been added to. Possible options are:
//   -2  User Contributed Pending Submission (not useful unless deleting user-contributed resources)
//   -1  User Contributed Pending Review (not useful unless deleting user-contributed resources)
//   1   Waiting to be Archived
//   2   Archived
//   3   Deleted (Recommended)
$resource_deletion_state = 3;

// Enable requring password entry to delete single resources? FALSE by default as resources are not really deleted,
//  they are simply moved to a deleted state which is less dangerous, see $resource_deletion_state above.
$delete_requires_password = false;


// RESOURCE ANNOTATION SETTINGS
// Enable annotation of image or document previews? Annotations are linked to nodes, the user needs to specify which
//  field a note is bind to.
$annotate_enabled = false;

// Array of fields used to bind to annotations.
$annotate_fields = array();

// Enable annotations in read-only mode?
$annotate_read_only = false;

// Enable anonymous users to add, edit, or delete annotations?
$annotate_crud_anonymous = false;


// RESOURCE REQUEST SETTINGS
// Enable the Research Request functionality?  Allows users to request resources via a form, which is emailed.
$research_request = false;

// Enable sending confirmation emails to user when request sent or assigned?
$request_senduserupdates = true;

// If set, which field will cause warnings to appear when approving requests containing these resources?
# $warn_field_request_approval = 115;

// Enable when requesting a resource or resources, is the "Reason for Request" field mandatory?
$resource_request_reason_required = true;

// Enable preventing users without accounts from requesting resources when accessing external shares? If TRUE,
//  external users requesting access will be redirected to the login screen, so only recommended if account requests
//  are allowed.
$prevent_external_requests = false;

// Enable option to remove all resources from the current collection once it has been requested?
$collection_empty_on_submit = false;

// Enable the 'Request' button on resources adds the item to the current collection that can then be requested,
//  instead of starting a request process for this individual item?
$request_adds_to_collection = false;

// Remove 'Never' option for resource request access expiration and set default expiry date to 7 days?
$removenever = false;

// Uncomment and set the comma separated field names to enable additional custom fields that are collected and emailed
//  when new resources or collections are requested.
# $custom_request_fields = "Phone Number, Department";

// List of custom fields that are required
# $custom_request_required = "Phone Number";

// Set that particular fields are displayed in different ways, options:
//   1 - Normal text box (default).
//   2 - Large text box.
//   3 - Drop down box, set options using $custom_request_options["Field Name"]=array("Option 1","Option 2","Option 3");
//   4 - HTML block, e.g. help text paragraph (set HTML usign $custom_request_html="<b>Some HTML</b>";
# $custom_request_types["Department"] = 1;

// Optional setting to override the default $email_notify address for resource request email notifications, applies to
//  specified resource types. Can be used so that along with the users/emails specified by
//  $resource_type_request_emails, the rest of the users can be notified as well.
//  Photo Example (resource type 1 by default): $resource_type_request_emails[1] = "imageadministrator@my.site";
//  Document Example (resource type 2 by default): $resource_type_request_emails[2] = "documentadministrator@my.site";
$resource_type_request_emails_and_email_notify = false;

// Manage requests automatically using $manage_request_admin[resource type ID] = user ID; IMPORTANT: the admin user
//  needs to have permissions R and Rb set otherwise this will not work.
# $manage_request_admin[1] = 1; # Photo
# $manage_request_admin[2] = 1; # Document
# $manage_request_admin[3] = 1; # Video
# $manage_request_admin[4] = 1; # Audio


// RESOURCE RATING SETTINGS
// Enable user rating of resources? Users can rate resources using a star ratings system on the Resource View page.
//  Average ratings are automatically calculated and used for the 'popularity' search ordering.
$user_rating = false;

// Enable allowing each user only one rating per resource (can be edited). This will remove all accumlated ratings and
//  weighting on newly rated items.
$user_rating_only_once = true;

// If $user_rating_only_once=true, enable a log view of user's ratings (link is in the rating count on the View page)?
$user_rating_stats = true;

// Enable users to remove their rating?
$user_rating_remove = true;

// Legacy option that allows for selection of a metadata field that contains administrator ratings, not user ratings,
//  that will be displayed in search list view. Field must be plain text and have numeric only numeric values.
# $rating_field = 121;


// RESOURCE COMMENTING SETTINGS
// Enable users to make comments on resources?
$comments_resource_enable=false;

// Enable collection commenting and ranking?
$collection_commenting = false;

// Enable users to make comments on collections (reserved for future use)?
# $comments_collection_enable = false;

// Enable showing in a threaded, indented view?
$comments_flat_view = false;

// Maximum number of nested comments or threads.
$comments_responses_max_level = 10 ;

// Maximum number of characters for a comment.
$comments_max_characters = 200;

// Email address to use for flagged comment notifications.
$comments_email_notification_address = "";

// Enable anonymous commenter's email address private?
$comments_show_anonymous_email_address = false;

// If set, popup a new window fulfilled by URL (when clicking on "comment policy" link).
$comments_policy_external_url = "";

// Display an astrisk by the comment view panel title if comments exist?
$comments_view_panel_show_marker = true;


// SPEEDTAGGING SETTINGS
// DEVELOPMENTAL: Enable speed tagging feature?
$speedtagging = false;

// Resource field ID to use for speed tagging.
$speedtaggingfield = 1;

// To set speed tagging field by resource type, set $speedtagging_by_type[resource_type]=resource_type_field;
//  default will be $speedtaggingfield.  Example to add speed tags for Photo type(1) to the Caption(18) field:
//  $speedtagging_by_type[1] = 18;


//----CONTACT SHEET SETTINGS--------------------------------------------------------------------------------------------
// Enable Contact Sheet feature? Requires ImageMagick and Ghostscript.
$contact_sheet = true;

// Create a separate resource file when creating contact sheets?
$contact_sheet_resource = false;

// If $contact_sheet_resource=true, resource type to create for the new resource file.
$contact_sheet_resource_type = 1;

// Enable AJAX previews in contact sheet configuration?
$contact_sheet_previews = true;

// If $contact_sheet_previews=true, the preview image size in pixels.
$contact_sheet_preview_size = "500x500";

// Contact Sheet font. Options: "helvetica" ," times" , "courier" (standard), and "dejavusanscondensed" (for more
//  Unicode support, but embedding/subsetting makes it slower). There are also several other fonts included in the TCPDF
//  library, but not ResourceSpace, which provide Unicode support. To embed other fonts, acquire the files from the
//  TCPDF distribution or create your own using TCPDF utilities, and install them in the ../lib/tcpdf/fonts folder.
//  If you encounter issues with Chinese characters, use "arialunicid0" and ensure Ghostscript has the ArialUnicodeMS
//  font (on Windows servers, this should be there already).
$contact_sheet_font = "helvetica";

// Enable Unicode filenames? Stripped out by default in TCPDF, but since collection names may have special characters,
//  default is TRUE.
$contact_sheet_unicode_filenames = true;

// Set Contact Sheet title font size.
$titlefontsize = 20;

// Set Contact Sheet field and resource ID font size.
$refnumberfontsize = 14;

// Array of list-style Contact Sheet fields.
$config_sheetlist_fields = array(8);

// Display Resource ID in list-style Contact Sheet?
$config_sheetlist_include_ref = true;

// Array of thumbnail-style Contact Sheet fields.
$config_sheetthumb_fields = array();

// Display Resource ID in thumbnail-style Contact Sheet?
$config_sheetthumb_include_ref = true;

// Array of single resource Contact Sheet fields.
$config_sheetsingle_fields = array(8);

// Enable single resource Contact Sheets?
$contact_sheet_single_select_size = false;

// Display Resource ID in single resource Contact Sheet?
$config_sheetsingle_include_ref = true;

// Use templates rather than setting Contact Sheet fields by display style?
$contactsheet_use_field_templates = false;

// If using Contact Sheet templates, set: 'name' = displayed name of the template and 'fields' = array of fields to use.
//  Fields will be displayed in setting order.
# $contactsheet_field_template[0]['name'] = 'Title only';
# $contactsheet_field_template[0]['fields'] = array(8);

# $contactsheet_field_template[0]['name'] = 'Title & Filename';
# $contactsheet_field_template[0]['fields']  array(8, 51);

// Enable Contact Sheet sorting (experimental, does not include ASC/DESC)?
$contactsheet_sorting = false;

// Enable header text to Contact Sheet?
$contact_sheet_include_header = true;

// If $contact_sheet_include_header=true, give user the option to add header text to Contact Sheet?
$contact_sheet_include_header_option = false;

// Display the application name in the Contact Sheet header?
$contact_sheet_include_applicationname = true;

// Enable a logo image to the Contact Sheet?
$include_contactsheet_logo = false;

// If $include_contactsheet_logo=true, enable an option to add or remove the logo?
$contact_sheet_logo_option = true;

// If $include_contactsheet_logo=true, set the logo path to a PNG, GIF, JPG, or PDF file.
# $contact_sheet_logo = "gfx/contactsheetheader.png";

// Enable scaling the logo to a hardcoded percentage of the page size?  Otherwise, the image is sized at 300ppi or the
//  PDF retains its original dimensions.
$contact_sheet_logo_resize = true;

// Display Contact Sheet footer ($contact_sheet_custom_footerhtml removed, handled in templates and enabled by either
//  showing or hiding the footer).
$contact_sheet_footer = false;

// Enable making Contact Sheet images links to the respective Resource View pages?
$contact_sheet_add_link = true;

// If $contact_sheet_add_link=true, enable option to enable links?
$contact_sheet_add_link_option = false;

// Enable option to display a field name in front of field data?
$contact_sheet_field_name_option = false;

// Enable bolding the resource field names if shown?
$contact_sheet_field_name_bold = false;

// Enable forcing watermarked previews for Contact Sheets?
$contact_sheet_force_watermarks = false;

// Enable option to force preview watermarks?
$contact_sheet_force_watermark_option = false;

// Display times in the dates in the Contact Sheet?
$contact_sheet_date_include_time = true;

// Enable Contact Sheet wordy dates?
$contact_sheet_date_wordy = true;

// Display Contact Sheet metadata under preview images for the thumbnail view?
$contact_sheet_metadata_under_thumbnail = false;

// Contact Sheet paper size options, to add a custom size, add a new line with the size as the value attribute in
//  "<WIDTH>x<HEIGHT>" format in millimeters.  Example: <option value = "216x343">Foolscap</option>
$papersize_select = '
    <option value="a4">A4 - 210mm x 297mm</option>
    <option value="a3">A3 - 297mm x 420mm</option>
    <option value="letter">US Letter - 8.5" x 11"</option>
    <option value="legal">US Legal - 8.5" x 14"</option>
    <option value="tabloid">US Tabloid - 11" x 17"</option>';

// Optional array to set customised title and margins for named templates.
# $contact_sheet_custom_size_settings = array('label' => array("title" => "ResourceSpace default label title", "margins" => "0, 0, 0, 0"));

// Contact Sheet column options (may want to limit if you are adding text fields to the Thumbnail style contact sheet).
$columns_select = '
    <option value=2>2</option>
    <option value=3>3</option>
    <option value=4 selected>4</option>
    <option value=5>5</option>
    <option value=6>6</option>
    <option value=7>7</option>';


//----ECOMMERCE SETTINGS------------------------------------------------------------------------------------------------
// Size-based pricing information, so the user can select the download size they require.
$pricing["scr"] = 10;
$pricing["lpr"] = 20;
$pricing["hpr"] = 30; # HPR is usually the original file download.

// PayPal settings.
$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
$payment_address = "payment.address@goes.here"; # Must enable Instant Payment Notifications in Paypal Account Settings.

// Payment currency and symbol.
$payment_currency = "GBP";
$currency_symbol = "&pound;";

// Should the "Add to Basket" function appear on the download sizes, so the size of the file required is selected
//  earlier and stored in the basket? This means the total price can appear in the basket.
$basket_stores_size = true;


// IIIF SETTINGS
// Enable the IIIF interface? See http://iiif.io for information on the IIIF standard. If TRUE, set a URL rewrite rule
//  or similar on the web server for any paths under the <base_url>/iiif path.
$iiif_enabled = false;

// User ID to use for IIIF. This user should be granted access only to those resources that are to be published via IIIF using permissions and a search filter.
# $iiif_userid = 0;

// IIIF identifier resource field ID; if using TMS, this may be the same as the TMS object field.
# $iiif_identifier_field = 29;

// IIIF description resource field ID.
# $iiif_description_field = 0;

// Field used that contains license information about the resource.
# $iiif_license_field = 0;

// Field that defines the position of a particular resource in the default sequence (only one sequence supported).
# $iiif_sequence_field = 1;

// Optional prefix that will be added to sequence identifier, useful if just numeric identifers are used e.g. for different views or pages .
# $iiif_sequence_prefix = "View ";

// Enable IIIF custom sizes?  Set to TRUE to support the Mirador/Universal viewer that requires the ability to request
//  arbitrary sizes by 'w,', ',h'  Note that this can result in significantly more storage space being required for
//  each resource published via IIIF, see https://iiif.io/api/image/2.1 for more information.
$iiif_custom_sizes = false;

$iiif_max_width  = 1024;
$iiif_max_height = 1024;

// Enable preview tiles (currently only used by IIIF when $iiif_level=1)?
$preview_tiles = false;

// Enable creating tiles along with normal previews?  If enabling IIIF on an existing system, then recommended to add
//  all IIIF published resources to a collection first and use the ../batch/recreate_previews.php script.
$preview_tiles_create_auto = true;

// Size in pixels of the tiles. The same value is used for both tile width and height (see
//  https://iiif.io/api/image/2.1/#region for more info).
$preview_tile_size = 1024;

// Array of available tile scale factors (see https://iiif.io/api/image/2.1/#size).
$preview_tile_scale_factors = array(1, 2, 4, 8, 16);


// FACIAL RECOGNITION SETTINGS
// Enable facial recognition?  Requires OpenCV and Python (version 2.7.6), credit to “AT&T Laboratories, Cambridge” for
//  their database of faces during initial testing phase.
$facial_recognition = false;

// Field that will be used to store the name of the person suggested or detected. IMPORTANT: field type MUST be dynamic keyword list.
$facial_recognition_tag_field = null;

// Physical file path to FaceRecognizer model state(s) and data. Security: best to place it outside of web root
//  IMPORTANT: ResourceSpace will not create this folder if it does not exist.
$facial_recognition_face_recognizer_models_location = '';



//----STATICSYNC FILE IMPORT SETTINGS-----------------------------------------------------------------------------------
// StaticSync (staticsync.php), ability to synchronise ResourceSpace with a separate and stand-alone filestore.

// Ref number of the user account that staticsync resources will be 'created by'.
$staticsync_userref = 1;

// StaticSync import folder.
$syncdir = "/dummy/path/to/syncfolder";

// List of folders to ignore within the sign folder.
$nogo = "[folder1]";

// Allow the system to specify the exact folders under the sync directory that need to be synced/ingested in
//  ResourceSpace.  Note: When using $staticsync_whitelist_folders and $nogo configs together, ResourceSpace is going
//  to first check the folder is in the $staticsync_whitelist_folders folders and then look in the $nogo folders.
$staticsync_whitelist_folders = array();

// Maximum number of files to process per execution of staticsync.php
$staticsync_max_files = 10000;

// Enable automatically creating themes based on the first and second levels of the sync folder structure?
$staticsync_autotheme = true;

// Enable unlimited theme levels to be created based on the folder structure? Will output a new $theme_category_levels
//  number which must then be updated in config.php.
$staticsync_folder_structure = false;

// Mapping extensions to resource types for synced files.
//  Format: staticsync_extension_mapping[resource_type]=array("extension 1","extension 2");
$staticsync_extension_mapping_default = 1;
$staticsync_extension_mapping[3] = array("mov", "3gp", "avi", "mpg", "mp4", "flv"); # Video
$staticsync_extension_mapping[4] = array("flv");

// Uncomment and set the next line to specify a category tree field to use to store the retieved path information for
//  each file. The tree structure will be automatically modified as necessary to match the folder strucutre within the
//  sync folder (performance penalty).
# $staticsync_mapped_category_tree = 50;

// Uncomment and set the next line to specify a text field to store the retrieved path information for each file. This
//  is a time saving alternative to the option above.
# $staticsync_filepath_to_field = 100;

// Enable appending multiple mapped values instead of overwritting? This will use the same appending methods used when
//  editing fields. Not used on dropdown, date, category tree, datetime, or radio buttons.
$staticsync_extension_mapping_append_values = true;

// Uncomment and set the next line to specify specific fields for $staticsync_extension_mapping_append_values
# $staticsync_extension_mapping_append_values_fields = array();

// Should the generated resource title include the sync folder path? This will not be used if $view_title_field is set
//  to the same field as $filename_field.
$staticsync_title_includes_path = true;

// Should the synced resource files be 'ingested' i.e. moved into ResourceSpace's own filestore structure?  In this
//  scenario, the synced folder merely acts as an upload mechanism. If path to metadata mapping is used then this
//  allows metadata to be extracted based on the file's location.
$staticsync_ingest = false;

// Enable forcing ingest of existing files into filestore if switching from $staticsync_ingest=false to
//  $staticsync_ingest=true;
$staticsync_ingest_force = false;

// Enable image rotation automatically when not ingesting resources? If set to TRUE, you must also set
//  $imagemagick_preserve_profiles=true;
$autorotate_no_ingest = false;

// Default workflow state for imported files (-2 = pending submission, -1 = pending review, etc.)
$staticsync_defaultstate = 0;

// Archive state to set for resources where files have been deleted/moved from the sync folder.
$staticsync_deleted_state = 2;

// Optional array of archive states for which missing files will be ignored and not marked as deleted, useful when
//  using offline_archive plugin.
$staticsync_ignore_deletion_states = array(2, 3);

// StaticSync_revive_state, if set then deleted items that later reappear will be moved to this archive state.
# $staticsync_revive_state = -1;

// StaticSync Path to metadata mapping.  It is possible to take path information and map selected parts of the path to
//  metadata fields. For example, if you added a mapping for '/projects/' and specified that the second level should
//  be 'extracted' means that 'ABC' would be extracted as metadata into the specified field if you added a file to
//  '/projects/ABC/'.  Hence, meaningful metadata can be specified by placing the resource files at suitable positions
//   within the static folder heirarchy.  Example, repeat for every mapping you wish to set up:
#   $staticsync_mapfolders[] = array(
#       "match" => "/projects/",
#       "field" => 10,
#       "level" => 2
#       );

// You can also now enter "access" in "field" to set the access level for the resource. The value must match the name
//  of the access level in the default local language. Note that custom access levels are not supported. For example,
//  the mapping below would set anything in the projects/restricted folder to have a "Restricted" access level.
#   $staticsync_mapfolders[] = array(
#       "match" => "/projects/restricted",
#       "field" => "access",
#       "level" => 2
#       );

// You can enter "archive" in "field" to set the archive state for the resource. You must include "archive" to the
//  array and its value must match either a default level or a custom archive level. The mapped folder level does not
//  need to match the name of the archive level. Note that this will override $staticsync_defaultstate. For example,
//  the mapping below would set anything in the restricted folder to have an "Archived" archive level.
#   $staticsync_mapfolders[] = array(
#       "match" => "/projects/restricted",
#       "field" => "archive",
#       "level" = >2,
#       "archive"=>2
#       );

// Alternative Files - There are a number of options for adding alternative files automatically using StaticSync.
//   These only work when s$taticsync_ingest=true.

// OPTION 1 - USE A SUBFOLDER WITH SAME NAME AS PRIMARY FILE
// If staticsync finds a folder in the same directory as a file with the same name as a file but with this suffix
//  appended, then files in the folder will be treated as alternative files for the given file. NOTE: Alternative file processing only works when $staticsync_ingest=true. Example: a folder/file structure might look like:
# /staticsync_folder/myfile.jpg
# /staticsync_folder/myfile.jpg_alternatives/alternative1.jpg
# /staticsync_folder/myfile.jpg_alternatives/alternative2.jpg
# /staticsync_folder/myfile.jpg_alternatives/alternative3.jpg
# $staticsync_alternatives_suffix = "_alternatives";

// OPTION 2 - ADD FILES IN SAME FOLDER WITH DEFINED STRING SUFFIX
// Option to have alternative files located in same directory as primary files, but identified by a defined string. As
//  with staticsync_alternatives_suffix this only works when $staticsync_ingest=true. Can instead use
//  $staticsync_alt_suffix_array.
# $staticsync_alternative_file_text = "_alt_";

// OPTION 3 - ADD FILES IN SAME FOLDER WITH VARIOUS STRING SUFFIXES
// $staticsync_alt_suffixes / $staticsync_alt_suffix_array, these can be used instead of
// $staticsync_alternatives_suffix to support mapping suffixes to the names used for the alternative files.
# $staticsync_alt_suffixes = true;
# $staticsync_alt_suffix_array = array(
#    '_alt' => "",
#   '_verso' => "Verso",
#   '_dng' => "DNG",
#   '_orig' => "Original Scan",
#   '_tp' => "Title Page",
#   '_tpv' => "Title Page Verso",
#   '_cov' => "Cover",
#   '_ex' => "Enclosure",
#   '_scr' => "Inscription"
#   );
# $numeric_alt_suffixes = 8;

// Optionally, ignore files that are not at least this many seconds old.
# $staticsync_file_minimum_age = 120;

// Use the title embeded in the file metadata? If FALSE, the system will always synthesize a title from the filename and path, even if an embedded title is found.
$staticsync_prefer_embedded_title = true;

// Enable deletion of files located in $syncdir through the UI?
$staticsync_allow_syncdir_deletion = false;


//----DEPREDICATED CONFIGURATION OPTIONS--------------------------------------------------------------------------------
$email_notify="resourcespace@my.site"; # Where resource/research/user requests are sent.
$email_notify_usergroups=array(); # Use of email_notify is deprecated as system notifications are now sent to the appropriate users based on permissions and user preferences. This variable can be set to an array of usergroup references and will take precedence.

// Legacy Tile options, The home_dash option and functionality has replaced these config options.
# Options to show/hide the tiles on the home page
$home_themeheaders=false;
$home_themes=true;
$home_mycollections=true;
$home_helpadvice=true;
$home_advancedsearch=false;
$home_mycontributions=false;

# Custom panels for the home page.
# You can add as many panels as you like. They must be numbered sequentially starting from zero (0,1,2,3 etc.)
# You may want to turn off $home_themes etc. above if you want ONLY your own custom panels to appear on the home page.
# The below are examples.

# $custom_home_panels[0]["title"]="Custom Panel A";
# $custom_home_panels[0]["text"]="Custom Panel Text A";
# $custom_home_panels[0]["link"]="search.php?search=example";
# You can add additional code to a link like this:
# $custom_home_panels[0]["additional"]="target='_blank'";
# $custom_home_panels[1]["title"]="Custom Panel B";
# $custom_home_panels[1]["text"]="Custom Panel Text B";
# $custom_home_panels[1]["link"]="search.php?search=example";
# $custom_home_panels[2]["title"]="Custom Panel C";
# $custom_home_panels[2]["text"]="Custom Panel Text C";
# $custom_home_panels[2]["link"]="search.php?search=example";

$title_sort=false; // deprecated, based on resource table column
$country_sort=false; // deprecated, based on resource table column
$original_filename_sort=false; // deprecated, based on resource table column

# ZIP command to create ZIP archive (uncomment to enable download of collections as a ZIP file).
# $zipcommand =
# This setting is deprecated and replaced by $collection_download and $collection_download_settings.

// Enable captioning and ranking of collections (deprecated - use $collection_commenting instead)
$collection_reorder_caption=false;

$date_column=false; // based on creation_date which is a deprecated mapping. The new system distinguishes creation_date (the date the resource record was created) from the date metadata field. creation_date is updated with the date field.
