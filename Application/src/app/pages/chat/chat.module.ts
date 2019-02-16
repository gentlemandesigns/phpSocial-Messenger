import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Routes, RouterModule } from '@angular/router'
import { PhotoViewer } from '@ionic-native/photo-viewer/ngx';

import { IonicModule } from '@ionic/angular';

import { ChatPage } from './chat.page';
import { ChatbubbleComponent } from "../../components/chatbubble/chatbubble.component";

const routes: Routes = [
  {
    path: '',
    component: ChatPage
  }
];

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    RouterModule.forChild(routes)
  ],
  declarations: [ChatPage, ChatbubbleComponent],
  providers: [
    PhotoViewer,
  ]
})
export class ChatPageModule {}
