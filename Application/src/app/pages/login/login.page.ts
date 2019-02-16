import { Component, OnInit } from '@angular/core';
import { NavController, LoadingController  } from "@ionic/angular";
import { Http } from "@angular/http";
import { AuthentificationService } from '../../services/authentification/authentification.service';
import { API_URL, LANGUAGE } from '../../../environments/environment';
import { OneSignal } from '@ionic-native/onesignal/ngx';

@Component({
  selector: 'app-login',
  templateUrl: './login.page.html',
  styleUrls: ['./login.page.scss'],
})
export class LoginPage implements OnInit {
  language = LANGUAGE;

  username: String;
  password: String;
  loading: boolean = false;
  error;

  constructor(
    private http: Http, 
    private auth: AuthentificationService,
    private nav: NavController,
    private oneSignal: OneSignal,
  ) {
    if(this.auth.isLoghed()){
      this.nav.navigateRoot('/app');
    }
  }

  ngOnInit() {
  }

  onSubmit(){
    let credentials = {
      username: this.username,
      password: this.password,
    }

    this.loading = true;

    this.http.post(API_URL+'login', credentials).subscribe( 
      result => {
        let res = result.json();

        if( res.status == 'success' ){
          let user = res.data;
          this.auth.setToken(res.data.token);
          delete user.token;
          this.auth.setUser(user);
          this.oneSignal.sendTags({
            userId: user.id,
            username: user.username
          });
          this.oneSignal.setSubscription(true);
          this.nav.navigateRoot('/app');
        }
      },
      error => {
        let err = JSON.parse(error._body);
        if( err && err.status ){
          this.error = err.message;
        }
        this.loading = false;
      }
    );
  }

}
