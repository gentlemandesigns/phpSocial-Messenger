import { Component, OnInit } from '@angular/core';

import { AuthentificationService } from "../../services/authentification/authentification.service";
import { LANGUAGE } from '../../../environments/environment';
import { PhotoViewer } from '@ionic-native/photo-viewer/ngx';

@Component({
  selector: 'app-settings',
  templateUrl: './settings.page.html',
  styleUrls: ['./settings.page.scss'],
})
export class SettingsPage implements OnInit {
  language = LANGUAGE;

  settings: { notifications: boolean};

  constructor(
    public auth: AuthentificationService,
    private photoViewer: PhotoViewer,
  ) {
    this.settings = this.auth.getSettings();
  }

  ngOnInit() {

  }

  toggleNotifications(){
    this.auth.setSettings(this.settings);
  }

  showPhoto(){
    this.photoViewer.show(this.auth.getUser()['avatar']);
  }

  showCover(){
    this.photoViewer.show(this.auth.getUser()['cover']);
  }

}
