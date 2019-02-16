import { Component, OnInit } from '@angular/core';
import { Http, Headers } from '@angular/http';
import { AuthentificationService } from '../../services/authentification/authentification.service';
import { ActivatedRoute } from '@angular/router';
import { API_URL, LANGUAGE } from '../../../environments/environment';
import { PhotoViewer } from '@ionic-native/photo-viewer/ngx';

@Component({
  selector: 'app-user-details',
  templateUrl: './user-details.page.html',
  styleUrls: ['./user-details.page.scss'],
})
export class UserDetailsPage implements OnInit {
  language = LANGUAGE;

  userId: number;
  user: { idu, username, email, name, first_name, last_name, country, location, address, work, school, website, bio, facebook, twitter, gplus, avatar, cover, private, suspended, verified, gender, interests, born, blocked } = null;

  headers: Headers = null;

  constructor(
    private http: Http, 
    private auth: AuthentificationService,
    private route: ActivatedRoute,
    private photoViewer: PhotoViewer,
  ) {
    this.userId = this.route.snapshot.params.id;
  }

  ngOnInit(){

  }

  ionViewWillEnter() {
    this.headers = new Headers();
    this.headers.append('Authorization', 'Bearer '+this.auth.getToken());

    this.http.post(API_URL+'user/info/'+this.userId, {
      user: (this.auth.getUser())['id']
    }, {
      headers: this.headers
    }).subscribe( 
      result => {
        let res = result.json();
        if( res && res.status == 'success' ){
          this.user = res.data;
        }
      },
      error => {
        let err = JSON.parse(error._body);
        if( err && err.status ){
          // console.error(err.message);
        }
      }
    );
  }

  blockUser(){
    this.headers = new Headers();
    this.headers.append('Authorization', 'Bearer '+this.auth.getToken());

    this.http.post(API_URL+'user/block/'+this.userId, {
      user: (this.auth.getUser())['id']
    }, {
      headers: this.headers
    }).subscribe( 
      result => {
        let res = result.json();
        if( res && res.status == 'success' ){
          this.user['blocked'] = true;
        }
      },
      error => {
        let err = JSON.parse(error._body);
        if( err && err.status ){
          // console.error(err.message);
        }
      }
    );
  }

  showPhoto(){
    this.photoViewer.show(this.user['avatar']);
  }

  showCover(){
    this.photoViewer.show(this.user['cover']);
  }

}
