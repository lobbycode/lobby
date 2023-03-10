<?php

namespace App\Http\Controllers\Auth;
use Auth;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;
use App\Models\SocialMediaSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

class SocialMediaAuthController extends Controller
{
    /*public function __construct()
    {
        $this->middleware('IsAdmin');
    }*/
    public $_SOCIAL_MEDIA_SETTINGS;
    public function redirect($provider){
            return Socialite::driver($provider)->redirect();
    }
    public function callback($provider){
            $user = Socialite::driver($provider)->user();
           // dd($user);
            $user = User::firstOrCreate([
                   'email' => $user->email
            ],[
              'name' => $user->name,
              'password'=>Hash::make(Str::random(10)),
              'avatar' => $user->picture,
              
            ]);
            Auth::login($user);
            return redirect('/');
    }
    public function socialshare($request){
      dd($request);
      $url = 'http://127.0.0.1:8000/article/%D8%B3%D8%A4%D8%A7%D9%84';
      $text = 'hi';
      return view('socialshare', compact('url', 'text'));
    }
    public function handleProviderCallback_facebook_poster(){
        session_start();
        $this->_SOCIAL_MEDIA_SETTINGS=SocialMediaSetting::first();
        $APP_ID=json_decode($this->_SOCIAL_MEDIA_SETTINGS->facebook,TRUE)['APP_ID'];
        $CLIENT_SECRET=json_decode($this->_SOCIAL_MEDIA_SETTINGS->facebook,TRUE)['CLIENT_SECRET'];
        $PAGE_ID=json_decode($this->_SOCIAL_MEDIA_SETTINGS->facebook,TRUE)['PAGE_ID'];
        $REDIRECT_URL=json_decode($this->_SOCIAL_MEDIA_SETTINGS->facebook,TRUE)['REDIRECT_URL'];
        $FB_TOKEN=json_decode($this->_SOCIAL_MEDIA_SETTINGS->facebook,TRUE)['FB_ACCESS_TOKEN'];
        $fb = new \Facebook\Facebook([
          'app_id' => $APP_ID,
          'app_secret' => $CLIENT_SECRET,
          'default_graph_version' => 'v2.10',
        ]);

            
        $helper = $fb->getRedirectLoginHelper();
        

        try {
          $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exception\ResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exception\SDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }

        if (! isset($accessToken)) {
          if ($helper->getError()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: " . $helper->getError() . "\n";
            echo "Error Code: " . $helper->getErrorCode() . "\n";
            echo "Error Reason: " . $helper->getErrorReason() . "\n";
            echo "Error Description: " . $helper->getErrorDescription() . "\n";
          } else {
            header('HTTP/1.0 400 Bad Request');
            echo 'Bad request';
          }
          exit;
        }


       

        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
       /* echo '<h3>Metadata</h3>';
        var_dump($tokenMetadata);*/

        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateAppId($APP_ID);
        // If you know the user ID this access token belongs to, you can validate it here
        //$tokenMetadata->validateUserId('123');
        $tokenMetadata->validateExpiration();

        if (! $accessToken->isLongLived()) {
          // Exchanges a short-lived access token for a long-lived one
          try {
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
          } catch (Facebook\Exception\SDKException $e) {
            echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
            exit;
          }
        }
        session(['fb_access_token'=>(string) $accessToken]);

        $_SOCIAL_MEDIA_SETTINGS_FACEBOOK=SocialMediaSetting::first()->facebook;
        $_SOCIAL_MEDIA_SETTINGS_FACEBOOK=json_decode($_SOCIAL_MEDIA_SETTINGS_FACEBOOK,TRUE);

        $page_access_token=Http::get("https://graph.facebook.com/".$_SOCIAL_MEDIA_SETTINGS_FACEBOOK["PAGE_ID"]."?fields=access_token&access_token=".$accessToken->getValue())->json();
    
        
        
        $_SOCIAL_MEDIA_SETTINGS_FACEBOOK["FB_ACCESS_TOKEN"]=$accessToken->getValue();
        $_SOCIAL_MEDIA_SETTINGS_FACEBOOK["PAGE_ACCESS_TOKEN"]=$page_access_token["access_token"];
        SocialMediaSetting::where('id','<>',0)->update(['facebook'=>$_SOCIAL_MEDIA_SETTINGS_FACEBOOK]);
        //dd($accessToken->getValue());
        //dd($helper->getPageId());
        return redirect("/admin/schedule-posts-posts")->with('data',['alert'=>"Now (Publishing) Is Authenticated To Post Posts On Facebook","alert-type"=>"success"]);
    }
    public function handleProviderCallback_linkedin_poster(Request $request){
      
        $_SOCIAL_MEDIA_SETTINGS_LINKEDIN=SocialMediaSetting::first()->linkedin;
        $_SOCIAL_MEDIA_SETTINGS_LINKEDIN=json_decode($_SOCIAL_MEDIA_SETTINGS_LINKEDIN,TRUE);
        $_SOCIAL_MEDIA_SETTINGS_LINKEDIN["CODE"]=$request->code; 
        SocialMediaSetting::where('id','<>',0)->update(['linkedin'=>$_SOCIAL_MEDIA_SETTINGS_LINKEDIN]);

        $this->_SOCIAL_MEDIA_SETTINGS=SocialMediaSetting::first();
        $CLIENT_ID=json_decode($this->_SOCIAL_MEDIA_SETTINGS->linkedin,TRUE)['CLIENT_ID'];
        $CLIENT_SECRET=json_decode($this->_SOCIAL_MEDIA_SETTINGS->linkedin,TRUE)['CLIENT_SECRET'];
        $REDIRECT_URL=json_decode($this->_SOCIAL_MEDIA_SETTINGS->linkedin,TRUE)['REDIRECT_URL'];
        $CODE=json_decode($this->_SOCIAL_MEDIA_SETTINGS->linkedin,TRUE)['CODE'];
          $res = \Http::withHeaders(['content-type'=>'application/x-www-form-urlencoded'])->post("https://www.linkedin.com/oauth/v2/accessToken?grant_type=authorization_code&code=$CODE&redirect_uri=$REDIRECT_URL&client_id=$CLIENT_ID&client_secret=$CLIENT_SECRET"
        );
        $_SOCIAL_MEDIA_SETTINGS_LINKEDIN=SocialMediaSetting::first()->linkedin;
        $_SOCIAL_MEDIA_SETTINGS_LINKEDIN=json_decode($_SOCIAL_MEDIA_SETTINGS_LINKEDIN,TRUE);
        $_SOCIAL_MEDIA_SETTINGS_LINKEDIN["ACCESS_TOKEN"]=$res->json()['access_token'];
        $_SOCIAL_MEDIA_SETTINGS_LINKEDIN["REFRESH_ACCESS_TOKEN"]=$res->json()['refresh_token'];
        $_SOCIAL_MEDIA_SETTINGS_LINKEDIN["ACCESS_TOKEN_EXPIRATION_DATE"]=\Carbon::parse(now())->addDays(60);
        SocialMediaSetting::where('id','<>',0)->update(['linkedin'=>$_SOCIAL_MEDIA_SETTINGS_LINKEDIN]);

        return redirect("/admin/schedule-posts")->with('data',['alert'=>"Now (Nafezly Publishing) Is Authenticated To Post Posts On LinkedIn","alert-type"=>"success"]);
    }
    public function authenticate_facebook_application(){
        session_start();
        $this->_SOCIAL_MEDIA_SETTINGS=SocialMediaSetting::first();
        $APP_ID=json_decode($this->_SOCIAL_MEDIA_SETTINGS->facebook,TRUE)['APP_ID'];
        $CLIENT_SECRET=json_decode($this->_SOCIAL_MEDIA_SETTINGS->facebook,TRUE)['CLIENT_SECRET'];
        $PAGE_ID=json_decode($this->_SOCIAL_MEDIA_SETTINGS->facebook,TRUE)['PAGE_ID'];
        $REDIRECT_URL=json_decode($this->_SOCIAL_MEDIA_SETTINGS->facebook,TRUE)['REDIRECT_URL'];
        $FB_TOKEN=json_decode($this->_SOCIAL_MEDIA_SETTINGS->facebook,TRUE)['FB_ACCESS_TOKEN'];
        $fb = new \Facebook\Facebook([
          'app_id' => $APP_ID,
          'app_secret' => $CLIENT_SECRET,
          'default_graph_version' => 'v2.10', 
        ]);
        $helper = $fb->getRedirectLoginHelper();

        $permissions = ['pages_manage_posts','pages_read_user_content','email']; // Optional permissions
        $loginUrl = $helper->getLoginUrl($REDIRECT_URL, $permissions);
        return redirect($loginUrl);
    }
    public function authenticate_linkedin_application(){
        $this->_SOCIAL_MEDIA_SETTINGS=SocialMediaSetting::first();
        $CLIENT_ID=json_decode($this->_SOCIAL_MEDIA_SETTINGS->linkedin,TRUE)['CLIENT_ID'];
        $CLIENT_SECRET=json_decode($this->_SOCIAL_MEDIA_SETTINGS->linkedin,TRUE)['CLIENT_SECRET'];
        $REDIRECT_URL=json_decode($this->_SOCIAL_MEDIA_SETTINGS->linkedin,TRUE)['REDIRECT_URL'];
        $SCOPES=json_decode($this->_SOCIAL_MEDIA_SETTINGS->linkedin,TRUE)['SCOPES'];
        return redirect("https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id=".$CLIENT_ID."&redirect_uri=".$REDIRECT_URL."&scope=".$SCOPES.";");
    } 
}
