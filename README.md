
# phpSocial-Messenger
[phpSocial](http://bit.ly/2Na83bV) Messenger is the best messenger application for your [phpSocial](http://bit.ly/2Na83bV) network. Let your users talk to each other from their phone, with push notifications, snap pictures from the camera, see online / offline friends, all with a native feel Android application.

Compatible with Android, [phpSocial](http://bit.ly/2Na83bV) Messenger offers native feel to your messenger application. No webview to your website URL, no slowdown. 

**Login:** demo / demodemo 
**Android demo:** [Download APK File](https://www.dropbox.com/s/nrw87sbn6w47v8p/phpsocial.apk?dl=1)

### Requirements
-   Windows 8 or newer
-   Minimal knowledge of JSON structure and XML.
-   Time and focus on reading the instructions.

# Installation
## First steps **[[Video Tutorial]](https://www.youtube.com/watch?v=e9tV18ulmXo)**
### Required software to be installed on your machine

 1. https://nodejs.org
 2. Ionic CLI: https://ionicframework.com/getting-started#cli
 3. Android Studio:  https://developer.android.com/studio/

#### Backend
From the downloaded **ZIP** package, upload to your server the folder`/api`, in the root directory.
#### Application
In your application folder open a command window / power shell / terminal.
Install Ionic Dependencies: `npm install`
To upload the application to Play Store, you need to make it yours.

Open the file `config.xml` and edit the following fields:
1.  `<widget id="YOUR_APP_ID">` (e.g.: com.yourcompany.phpsocialmessenger)
2.  `<name>APP_NAME</name>` with your application name with  **NO SPACES**
3.  `<description>APP_DESCRIPTION</description>` with your application description
4.  `<author email="YOUR_EMAIL" href="YOUR_WEBSITE">YOUR_COMPANY</author>` with your email, your website and your company name.

Open the file `ionic.config.json` and edit the following fields:
1.  `"name": "APP_NAME"` with your application name

Open the file `package.json` and edit the following fields:
1.  `"name": "APP_NAME"` with your application name
2.  `"version": "APP_VERSION"` with your application version. Application version should be this exact form  `x.x.x`. Example:  `0.0.1`.
3.  `"author": "YOUR_NAME"` with your company name
4.  `"homepage": "YOUR_WEBSITE"` with your company website

Open the file `package-lock.json` and edit the following fields:
1.  `"name": "APP_NAME"` with your application name
2.  `"version": "APP_VERSION"` with your application version. Application version should be this exact form  `x.x.x`. Example:  `0.0.1`.

Add the platforms to the project:  `ionic cordova platform add android` for Android
Prepare the platforms:  `ionic cordova prepare android`

**Linking with the backend:**
1.  Open the folder`/src/environments`  and open the two files. The file within `.prod`  the name will be used on the  **LIVE**  application uploaded to Google Play. The other one is used for testing purpose.
2.  Edit the **API_URL**  with your website URL: `http://yourwebsite.com/api/` **with**  a trailing slash!

## Push Notifications with OneSignal **[[Video Tutorial]](https://www.youtube.com/watch?v=GPw9FQJWBcw)**
phpSocial Messenger uses OneSignal free service for push notifications.
Follow this steps in order to activate Push notifications on your application.
1.  Register on OneSignal: [https://onesignal.com/](https://onesignal.com/)
2.  Create an application on OneSignal following their instructions.  
    We are not documenting it very detailed here because of constant upcoming changes on their interface.
3.  After creating, open the application from your OneSignal page, go to **Settings page**  and then to **Keys & IDs tab**.
4.  This are the API Keys.

### Backend integration
Login to your FTP server and open the folder  `/api` . Open the file `config.php` and edit the following configuration: `$CONF['onesignal']['appid']`  with your OneSignal APP ID and `$CONF['onesignal']['restkey']` with your OneSignal REST API Key.
Open the folder `/requests` from your phpSocial installation and edit the file `post_chat.php` as following:
 1. At the bottom of the document, before the PHP closing tag (?>), add the following code: 
```
include __DIR__.'/../api/config.php';
include __DIR__.'/../api/language.php';
function sendMessage($message) {
    global $CONF, $LNG;
    $content = [
      "en" => (( $message['message'] && strlen($message['message']) > 0 )? $message['message']: $LNG['image'])
    ];
    $fields = array(
      'app_id' => $CONF['onesignal']['appid'],
      'data' => ['from' => $message['from']],
      'headings' => ['en' => $LNG['newmessage'],],
      'contents' => $content,
      'filters' => [
        [
          "field" => "tag", 
          "key" => "userId", 
          "relation" => "=", 
          "value" => $message['destination']
        ],
      ],
      'big_picture' => $message['image'],
      'ios_attachments' => ['id' => $message['image']],
    );
    $fields = json_encode($fields);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic '.$CONF['onesignal']['restkey']
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}
```
 2. Replace the following lines of code:
```
if(!empty($_POST['message']) && $_POST['message'] !== ' ' && isset($_POST['type']) == false) {
  echo $feed->postChat($_POST['message'], $_POST['id']);
} elseif(isset($_POST['type'])) {
  echo $feed->postChat($_POST['message'], $_POST['id'], $_POST['type'], (isset($_POST['value']) ? $_POST['value'] : null));
}
```
with:
```
if(!empty($_POST['message']) && $_POST['message'] !== ' ' && isset($_POST['type']) == false) {
  $response = $feed->postChat($_POST['message'], $_POST['id']);
  if( $response !== false ){
    $message = [
      'from' => $user['idu'],
      'destination' => $_POST['id'],
      'message' => $_POST['message'],
      'image' => '',
    ];
    sendMessage($message);
  }
  echo $response[0];
} elseif(isset($_POST['type'])) {
  $response = $feed->postChat($_POST['message'], $_POST['id'], $_POST['type'], (isset($_POST['value']) ? $_POST['value'] : null));
  if( $response !== false ){
    $message = [
      'from' => $user['idu'],
      'destination' => $_POST['id'],
      'message' => $_POST['message'],
      'image' => $CONF['url'].'/uploads/media/'.$response[1],
    ];
    sendMessage($message);
  }
  echo $response[0];
}
```
Open the folder  `/includes` and edit the file `classes.php`.
Search for the following line of code `function postChat($message, $uid, $type = null, $value = null)` and at the end of the function locate  `// Close the statement`. Replace the following code:
```
// Close the statement
$stmt->close();
if($affected) {
  return $this->getChatMessages($uid, null, null, 1);
}
```
with:
```
// Close the statement
$stmt->close();
if($affected) {
  return [$this->getChatMessages($uid, null, null, 1), $this->db->real_escape_string(strip_tags($value))];
}
```
#### Application integration
Open your phpSocial Messenger application folder and go to sub-folder `/src/environments`. Open for edit both files.
Edit the variables as following: **APPID** is your OneSignal APP ID, **GOOGLEPROJECTNUMBER**  is the project ID you’ve generated on creating the project, more details here: [https://documentation.onesignal.com/docs/generate-a-google-server-api-key](https://documentation.onesignal.com/docs/generate-a-google-server-api-key).

## Translating the Application
### Backend
Login to your FTP server and open the folder  `/api` . Open and edit the file `language.php` with your strings.

### Application
Open your phpSocial Messenger application folder and go to sub-folder `/src/environments`. Open for edit both files.
The translation strings are located under the variable`LANGUAGE`.

## Changing colors & branding
### Colors
Changing colors of the application can be done by editing the file `variables.scss` from the folder `/src/theme`. The variables are  [CSS3 variables](https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_variables). Official Ionic Documentation: [https://beta.ionicframework.com/docs/theming/css-variables](https://beta.ionicframework.com/docs/theming/css-variables).

The file can be easily generated using the official Ionic color tool: [https://beta.ionicframework.com/docs/theming/color-generator](https://beta.ionicframework.com/docs/theming/color-generator)

### Branding
Logo can be changed by replacing the file `logo.svg` with your SVG file for your logo. Logo can be found in the folder `src/assets`.
Changing the icon and the splash screen can be done by editing the `icon.png` and `splash.png` from the folder  `/resources`. After changing the files, open a command-line window / terminal in your phpSocial Messenger folder and run the following command: `ionic resources`. This command will automatically generate the proper resources for your platforms.

## Compile application [[Video Tutorial]](https://www.youtube.com/watch?v=ZvNuzR2GB2w)
First, follow the  [First steps](http://docs.gentlemandesigns.com/first-steps/)  tutorial.
We can compile them by typing the command: `ionic cordova build android --prod --release`
The compiled files should be found in: `\platforms\android\app\build\outputs\apk\release\app-release.apk`  for Android
Download APK Signer for Windows: [https://shatter-box.com/download/apk-signer/](https://shatter-box.com/download/apk-signer/)
Generate a keystore, sign the application and align it.
**You are ready to o publish !**
