import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Routes, RouterModule } from '@angular/router';
import { IonicModule } from '@ionic/angular';
import { PhotoViewer } from '@ionic-native/photo-viewer/ngx';

import { SettingsPage } from './settings.page';

const routes: Routes = [
  {
    path: '',
    outlet: 'settings',
    component: SettingsPage
  }
];

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    RouterModule.forChild(routes)
  ],
  declarations: [SettingsPage],
  providers: [
    PhotoViewer
  ]
})
export class SettingsPageModule {}
