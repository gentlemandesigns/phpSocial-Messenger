import { Injectable } from '@angular/core';
import { NavController } from "@ionic/angular";
import { OneSignal } from "@ionic-native/onesignal/ngx";


@Injectable({
  providedIn: 'root'
})
export class AuthentificationService {
  user: { idu, username, email, name, first_name, last_name, country, location, address, work, school, website, bio, facebook, twitter, gplus, avatar, cover, private, suspended, verified, gender, interests, born, blocked } = null;
  token: string = null;
  settings: { notifications: boolean } = { notifications: true };

  public dataLoaded: boolean = false;

  constructor(
    private nav: NavController, 
    private oneSignal: OneSignal,
  ) {
    try {
      this.token = localStorage.getItem('token') ? localStorage.getItem('token') : null;
      this.user = localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : null;
      this.settings = localStorage.getItem('settings') ? JSON.parse(localStorage.getItem('settings')) : { notifications: true };

      this.oneSignal.setSubscription( this.settings['notifications'] );

    } catch (error) {
      
    }
  }

  setToken(token){
    this.token = token;
    localStorage.setItem('token', token );
  }

  setUser(user){
    this.user = user;
    localStorage.setItem('user', JSON.stringify(user) );
  }

  setSettings(settings){
    this.settings = settings;
    localStorage.setItem('settings', JSON.stringify(settings) );
    this.oneSignal.setSubscription( this.settings['notifications'] );
  }

  isLoghed(): boolean {
    return ( this.user && this.token )? true: false;
  }

  getToken(){
    return this.token;
  }

  getUser(){
    return this.user;
  }

  getSettings(){
    return this.settings;
  }

  async logout(){
    this.token = null;
    this.user = null;
    localStorage.clear();
    this.oneSignal.deleteTags(['userId', 'username']);
    this.oneSignal.setSubscription(false);
    this.nav.navigateRoot('login');
  }
}
