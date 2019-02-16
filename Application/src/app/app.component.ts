import { Component } from '@angular/core';

import { Platform, NavController } from '@ionic/angular';
import { SplashScreen } from '@ionic-native/splash-screen/ngx';
import { StatusBar } from '@ionic-native/status-bar/ngx';
import { OneSignal } from "@ionic-native/onesignal/ngx";
import { Network } from '@ionic-native/network/ngx';

import { ONESIGNAL, LANGUAGE } from "../environments/environment";

@Component({
  selector: 'app-root',
  templateUrl: 'app.component.html'
})
export class AppComponent {
  constructor(
    private platform: Platform,
    private splashScreen: SplashScreen,
    private statusBar: StatusBar,
    private oneSignal: OneSignal,
    private nav: NavController,
    private network: Network
  ) {
    this.initializeApp();
  }

  initializeApp() {
    this.platform.ready().then(() => {
      this.statusBar.backgroundColorByHexString('#4d67a7');
      this.statusBar.styleLightContent();
      this.statusBar.overlaysWebView(false);
      this.splashScreen.hide();


      this.oneSignal.startInit(ONESIGNAL.APPID, ONESIGNAL.GOOGLEPROJECTNUMBER);
      this.oneSignal.inFocusDisplaying(this.oneSignal.OSInFocusDisplayOption.Notification);
      this.oneSignal.handleNotificationOpened().subscribe((data) => {
        this.nav.navigateForward('/chat/' + data.notification.payload.additionalData['from'] );
      });
      let disconnectSubscription = this.network.onDisconnect().subscribe(() => {
        alert(LANGUAGE['connectionDisconnected']);
        navigator['app'].exitApp();
      });
      this.oneSignal.endInit();
    });
  }
}
