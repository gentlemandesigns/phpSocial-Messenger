import { Component, OnInit } from '@angular/core';
import { Http, Headers } from '@angular/http';
import { LoadingController } from '@ionic/angular';
import { AuthentificationService } from '../../services/authentification/authentification.service';
import { API_URL, LANGUAGE } from '../../../environments/environment';
import { StatusBar } from '@ionic-native/status-bar/ngx';

@Component({
  selector: 'app-conversations-list',
  templateUrl: './conversations-list.page.html',
  styleUrls: ['./conversations-list.page.scss'],
})
export class ConversationsListPage implements OnInit {
  language = LANGUAGE;

  isLoading: boolean = true;

  conversations: Array<Object>;
  conversationsCounter: any;

  constructor(
    private http: Http, 
    private auth: AuthentificationService,
    public loadingController: LoadingController,
    private statusBar: StatusBar,
  ) {
    this.statusBar.overlaysWebView(false);
  }

  ngOnInit() {

  }

  ionViewDidEnter(){
    this.loadConversations();
    this.conversationsCounter = setInterval( () => {
      this.loadConversations();
    }, 1000*10);
  }

  ionViewWillLeave(){
    clearInterval(this.conversationsCounter);
  }

  loadConversations(){
    let headers = new Headers();
    headers.append('Authorization', 'Bearer '+this.auth.getToken());

    let data = { user: (this.auth.getUser())['id'] };

    this.http.post(API_URL+'messages', data, {
      headers: headers
    }).subscribe( 
      result => {
        let res = result.json();
        if( res && res.status == 'success' ){
          this.conversations = res.message;
          this.isLoading = false;
        }
      },
      error => {
        if(error.status > 200){
          this.auth.logout();
        }
      }
    );
  }

  readChat(conversation){
    let headers = new Headers();
    headers.append('Authorization', 'Bearer '+this.auth.getToken());

    this.http.post(API_URL+'messages/chat/markread', {
      to: (this.auth.getUser())['id'],
      from: conversation.from,
    }, {
      headers: headers
    }).subscribe( 
      result => {
        this.loadConversations();
      },
      error => {
        let err = JSON.parse(error._body);
        if( err && err.status ){
          // console.error(err.message);
        }
      }
    );
  }

  deleteChat(conversation){

    let headers = new Headers();
    headers.append('Authorization', 'Bearer '+this.auth.getToken());

    this.http.post(API_URL+'messages/chat/delete', {
      to: (this.auth.getUser())['id'],
      from: conversation.from,
    }, {
      headers: headers
    }).subscribe( 
      result => {
        this.loadConversations();
      },
      error => {
        let err = JSON.parse(error._body);
        if( err && err.status ){
          // console.error(err.message);
        }
      }
    );
  }

}
