import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { TabsPage } from './tabs.page';

import { ConversationsListPage } from "../conversations-list/conversations-list.page";
import { FriendsListPage } from "../friends-list/friends-list.page";
import { SettingsPage } from "../settings/settings.page";

const routes: Routes = [
  {
    path: 'tabs',
    component: TabsPage,
    children: [
      {
        path: '',
        redirectTo: '/app/tabs/(conversations:conversations)',
        pathMatch: 'full',
      },
      {
        path: 'conversations',
        outlet: 'conversations',
        component: ConversationsListPage,
      },
      {
        path: 'friends',
        outlet: 'friends',
        component: FriendsListPage,
      },
      {
        path: 'settings',
        outlet: 'settings',
        component: SettingsPage,
      }
    ]
  },
  {
    path: '',
    redirectTo: '/app/tabs/(conversations:conversations)',
    pathMatch: 'full'
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class TabsPageRoutingModule {}
