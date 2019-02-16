import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Routes, RouterModule } from '@angular/router';

import { IonicModule } from '@ionic/angular';

import { ConversationsListPage } from './conversations-list.page';

const routes: Routes = [
  {
    path: '',
    outlet: 'conversations',
    component: ConversationsListPage
  }
];

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    RouterModule.forChild(routes)
  ],
  declarations: [ConversationsListPage]
})
export class ConversationsListPageModule {}
