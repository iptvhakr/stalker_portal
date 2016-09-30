--

INSERT INTO `adm_grp_action_access`
        (`controller_name`,                          `action_name`,    `is_ajax`,  `description`)
VALUES  ('application-catalog',                                 '',            0, 'Application catalog. List of applications'),
        ('application-catalog',            'application-list-json',            1, 'Application catalog. List of applications  by page + filters'),
        ('application-catalog',                       'accept-tos',            0, 'Application catalog. Terms of use of  the catalog of applications Stalker'),
        ('application-catalog',               'application_detail',            0, 'Application catalog. Application info and list of available application versions'),
        ('application-catalog',   'application-get-data-from-repo',            1, 'Application catalog. Getting application info from repository'),
        ('application-catalog',                  'application-add',            1, 'Application catalog. Add new application by URL of the repository'),
        ('application-catalog',    'application-version-list-json',            1, 'Application catalog. List of application versions'),
        ('application-catalog',  'application-version-save-option',            1, 'Application Catalog. Editing the options of application'),
        ('application-catalog',      'application-version-install',            1, 'Application catalog. Install available application version'),
        ('application-catalog',       'application-version-delete',            1, 'Application catalog. Delete installed application version'),
        ('application-catalog',         'application-toggle-state',            1, 'Application catalog. Enable and disable application'),
        ('application-catalog',               'application-delete',            1, 'Application catalog. Delete application');

UPDATE `apps_tos` SET `tos_en` = '
<br><h1><span>Conditions of Use for Stalker Application Directory </span> </h1>

<br><h3><span>August 17, 2015</span></h3><br>

<h2><span>1. Introduction</span></h2>

<p><span>Stalker Application Directory (hereinafter referred to as the Directory) is an ordered list of applications provided by the application vendors that are available for installation in Middleware Stalker (hereinafter referred to as Stalker).</span>
</p>

<p><span>The Directory is provided by Telecommunication Technologies LLC (hereinafter referred to as the Company or we/us), which is located at the following address: 1 Tamozhennaya Square, city of Odessa, Odessa region, 65026.</span>
</p>

<p><span>In order to use the Directory it is required that you agree to the following Conditions of Use (hereinafter referred to as the Conditions). Please read them carefully. If any of their provisions are unclear or are unacceptable to you, you may not use the Directory. By starting to use the Directory, you accept the Conditions.</span>
</p>

<h2><span>2. Directory Use</span></h2>

<p><b><span>Access to the Directory</span></b><span><span>. You can use the Directory to install the applications to Stalker or other media. The application availability varies by country, and the entire list of applications will possibly not be available in your country</span></span><span>.</span>
</p>

<p><span><b>Applications. </b></span><span>The Company provides a platform in the form of Stalker interface, by which you can at your sole discretion produce a list of applications with those available in the Directory or add an application to the Directory yourself. In connection herewith, all relationships associated with your use of such applications, be it relations with the stakeholders (including, but not limited to, financial relations) or the relationship with the authorities and organizations that oversee the legality and morality issues within certain Content categories shall be regulated solely between you and such persons</span><span>.</span>
</p>

<p><span><b>Application sources.</b></span><span>The Company provides a list of applications, but does not limit you to installing the applications distributed on the Internet. These applications are developed by third parties unrelated to the Company, therefore the Company does not bear responsibility for the possible damage to the software, hardware, and user devices as a result of their use.</span>
</p>

<p><b><span>Content</span></b><span><span>(defined as data files, texts, software, music, audio files, photos, videos or other images) is always provided for you by your application vendor. The Company is not a distributor (licensee, provider, agent, etc.) of the Content. The Content is created by third parties that are not related to the Company, therefore the Company is not responsible for the Content created by third parties, and did not give its approval in relation to such Content</span></span><span>.</span>
</p>

<p><b><span>Age restrictions. </span></b><span>To use the Directory you must be 14 or older. If you are between 14 and 18, you need a written permission from your parents or legal guardians to use the Directory and accept the Conditions. If in accordance with the legislation of the country you conduct business in or you are going to access the Directory from you are not permitted or are prohibited by law to use an application or any Content, you must not access the Directory. You must abide by all the additional age restrictions, which may be applied for the use and distribution of the Content or applications of one kind or another.</span>
</p>

<p><b><span>Key Conditions of Use.</span></b><span>&nbsp;To use the Directory you need Stalker and a compatible Device, which meets the system requirements of the relevant application and Content that may be occasionally changed, as well as an Internet connection and compatible software. The use of applications and their efficiency depend on these factors. You shall bear responsibility for meeting the system requirements.</span>
</p>

<p><b><span>Payments to third parties.</span></b><span>&nbsp;Some third parties (e.g., an application vendor, stakeholder, ISP or mobile operator) may charge for data transfer or access when using applications. You shall always make all such payments yourself and shall be responsible for them.</span>
</p>

<p><b><span>Directory updating.</span></b><span><span> Our Company reserves the right to change the functionality or user interface design of the Directory from time to time. To use the Directory you may need to install such updates or software occasionally produced by our Company. The Directory may occasionally access the application source servers and check for the updates necessary for its operation, for example, patches, enhancements, missing plug-ins and new versions (hereinafter collectively referred to as the Updates)</span></span><span>.</span>
</p>

<h2><span>3. Rights and Restrictions </span></h2>

<p><b><span>Security functions.</span></b><span>&nbsp;You do not have the right to independently attempt to or authorize other persons, assist them and encourage them to traverse, disable or destroy any security functions or components, such as digital rights management or encryption protecting any application or Content (including the Directory) that prevent or otherwise restrict access to them. Violation of any security functions may become subject to a civil, administrative or criminal responsibility.</span>
</p>

<p><b><span>Defective Content</span></b><b><span>.</span></b><span><span>If you have any faults or problems during operation of the application, you should contact the application vendor</span></span><span>.</span>
</p>

<p><b><span>Application removal or unavailability.</span></b><span>&nbsp;The access to applications is provided through the Directory without time limitation. In some cases (for example, if the application vendor terminates the support of the applications or Content, if the application or Content violate the terms or laws applicable) the Company may remove the application from the Directory. If possible, the Company will send you an advance notice about the application or Content removal or termination of access.</span>
</p>

<p><b><span>Hazardous activity.</span></b><span>&nbsp;There are no applications or Content intended for use in the operation of nuclear facilities, life support systems, emergency communication, navigation or communication of aircrafts or flight control, as well as other activities, in which the failure of the application or Content may result in death, injury or serious damage to health or the environment.</span>
</p>

<p><b><span>Change in the Conditions. </span></b><span><span>In the event of changes in the Conditions, before the next use of the Directory you will be prompted to accept the new Conditions. Once you accept the new Conditions, they will apply to the entire Directory (including the previously installed applications) and all subsequent applications up to the new changes notification</span></span><span>.<br><br>Refusing to accept the changed Conditions, you may not use the Directory.</span>
</p>

<p><br>
</p>
';

-- //@UNDO

--