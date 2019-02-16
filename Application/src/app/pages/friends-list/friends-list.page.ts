import { Component, OnInit } from '@angular/core';
import { LoadingController } from "@ionic/angular";
import { Http, Headers } from "@angular/http";
import { AuthentificationService } from '../../services/authentification/authentification.service';
import { API_URL, LANGUAGE } from '../../../environments/environment';

@Component({
  selector: 'app-friends-list',
  templateUrl: './friends-list.page.html',
  styleUrls: ['./friends-list.page.scss'],
})
export class FriendsListPage implements OnInit {
  language = LANGUAGE;

  isLoading: boolean = true;

  friends: Array<Object> = [];
  friendsList: Array<Object> = [];
  searchText: string;

  constructor(
    private http: Http, 
    private auth: AuthentificationService,
    public loadingController: LoadingController
  ) {
      
  }

  ngOnInit() {
  }

  ionViewDidEnter(){
    let headers = new Headers();
    headers.append('Authorization', 'Bearer '+this.auth.getToken());

    let data = { user: (this.auth.getUser())['id'] };

    this.http.post(API_URL+'user/friends', data, {
      headers: headers
    }).subscribe( 
      result => {
        let res = result.json();
        if( res && res.status == 'success' ){
          this.friends = res.data;
          this.friendsList = res.data;
          this.isLoading = false;
        }
      },
      error => {
        this.auth.logout();
      }
    );
  }

  searchChange(){
    this.friends = this.friendsList.filter( val => {
      return ( val['name'].includes(this.searchText) || val['username'].includes(this.searchText) );
    });
  }

  searchClear(){
    this.friends = this.friendsList;
  }

}
