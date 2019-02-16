import { Component, OnInit, ViewChild } from '@angular/core';
import { Http, Headers } from '@angular/http';
import { LoadingController, Content } from '@ionic/angular';
import { AuthentificationService } from '../../services/authentification/authentification.service';
import { API_URL, LANGUAGE } from '../../../environments/environment';
import { ActivatedRoute } from '@angular/router';


import { Camera, CameraOptions } from "@ionic-native/camera/ngx";


@Component({
  selector: 'app-chat',
  templateUrl: './chat.page.html',
  styleUrls: ['./chat.page.scss']
})
export class ChatPage implements OnInit {
  @ViewChild(Content) content: Content;
  
  language = LANGUAGE;

  userId: number;
  user: { idu, username, email, name, first_name, last_name, country, location, address, work, school, website, bio, facebook, twitter, gplus, avatar, cover, private, suspended, verified, gender, interests, born, blocked } = null;
  chat: Object[] = [];
  inputFocused: boolean = false;
  listEnd: boolean = false;
  isLoading: boolean = false;

  sendingMessage: boolean = false;

  headers: Headers;

  message: {
    message: string,
    from: number,
    to: number,
    type: string,
    value: any,
  } = {
    message: '',
    from: null,
    to: null,
    type: '',
    value: '',
  };
  lastChat: any;

  loadUnreadIterator: any;

  loadPage = 0;
  loadPerPage = 10;

  constructor(
    private http: Http, 
    private auth: AuthentificationService,
    public loadingController: LoadingController,
    private route: ActivatedRoute,
    private camera: Camera,
  ) {
    this.userId = this.route.snapshot.params.id;
    
    this.message.from = (this.auth.getUser())['id'];
    this.message.to = this.userId;
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
          this.user = res.data
        }
      },
      error => {
        let err = JSON.parse(error._body);
        if( err && err.status ){
          // console.error(err.message);
        }
      }
    );

    this.loadChat(0).then( data => {
      this.chat.push( ...data );
      this.listEnd = ( data.length >= this.loadPerPage )? false: true;
      setTimeout( () => {
        this.content.scrollToBottom(300);
      }, 200 );
    });
    this.readChat();
  }

  ionViewDidEnter(){
    this.loadUnreadIterator = setInterval( () => {
      this.loadUnread();
    }, 1000 * 10);
  }

  ionViewWillLeave(){
    clearInterval(this.loadUnreadIterator);
  }

  loadChat(offset: number = 0, take: number = 10): Promise<Array<Object>> {
    return new Promise( (resolve, reject) => {
      this.http.post(API_URL+'messages/chat/pagination/'+take+'/'+offset, {
        user1: (this.auth.getUser())['id'],
        user2: this.userId,
      }, {
        headers: this.headers
      }).subscribe( 
        result => {
          let res = result.json();
          if( res && res.status == 'success' ){
            let chat = (res.message as Array<Object>).reverse();
            resolve(chat);
          }
        },
        error => {
          let err = JSON.parse(error._body);
          if( err && err.status ){
            // console.error(err.message);
            reject(err.message)
          }
        }
      );
    });
  }

  async loadUnread(){
    this.http.post(API_URL+'messages/chat/unread', {
      user1: (this.auth.getUser())['id'],
      user2: this.userId,
    }, {
      headers: this.headers
    }).subscribe( 
      result => {
        let res = result.json();
        if( res && res.status == 'success' && res.message && res.message.length > 0 ){
          let chat = (res.message as Array<Object>).reverse();
          this.chat.push(...chat);
          this.readChat();
          setTimeout( () => {
            this.content.scrollToBottom(300);
          }, 200 );
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

  loadMore(){
    this.loadPage += this.loadPerPage;
    this.isLoading = true;
    this.loadChat(this.loadPage).then( data => {
      if( data.length == 0 ){
        this.listEnd = true;
      } else {
        this.chat = [...data, ...this.chat];
      }
      this.isLoading = false;
    });
  }

  readChat(){
    this.http.post(API_URL+'messages/chat/markread', {
      to: (this.auth.getUser())['id'],
      from: this.userId,
    }, {
      headers: this.headers
    }).subscribe( 
      result => {
        
      },
      error => {
        let err = JSON.parse(error._body);
        if( err && err.status ){
          // console.error(err.message);
        }
      }
    );
  }

  focusInput(){
    this.inputFocused = true;
  }
  blurInput(){
    this.inputFocused = false;
  }

  sendMessage(){
    if ( this.message.message.trim().length < 1 && this.message.type !== 'picture' ) return;

    this.sendingMessage = true;

    this.http.post(API_URL+'messages/chat/post', this.message, {
      headers: this.headers
    }).subscribe( 
      result => {
        let res = result.json();
        if( res && res.status == 'success' ){
          this.chat.push(res.message);
          this.message.message = '';
          this.message.type = '';
          this.message.value = '';

          this.sendingMessage = false;

          setTimeout( () => {
            this.content.scrollToBottom(300);
          }, 200 );
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

  captureImage(){
    let options: CameraOptions = {
      quality: 100,
      sourceType: this.camera.PictureSourceType.CAMERA,
      mediaType: this.camera.MediaType.PICTURE,
      destinationType: this.camera.DestinationType.DATA_URL,
      encodingType: this.camera.EncodingType.JPEG,
      correctOrientation: true,
    }
    
    this.camera.getPicture(options).then((imageData) => {
      this.message.type = 'picture';
      this.message.value = 'data:image/jpeg;base64,' + imageData;
      this.sendMessage();

    }, (err) => {
      
    });
  }

  pickImage(){
    let options: CameraOptions = {
      quality: 100,
      sourceType: this.camera.PictureSourceType.PHOTOLIBRARY,
      mediaType: this.camera.MediaType.PICTURE,
      destinationType: this.camera.DestinationType.DATA_URL,
      encodingType: this.camera.EncodingType.JPEG,
      correctOrientation: true,
    }
    
    this.camera.getPicture(options).then((imageData) => {
      this.message.type = 'picture';
      this.message.value = 'data:image/jpeg;base64,' + imageData;
      this.sendMessage();

    }, (err) => {
      
    });
  }

  older(date1, date2, days: number = 7){
    return (new Date(date1).getTime() < (new Date(date2).getTime() - (days * 24 * 60 * 60 * 1000) ));
  }

}
