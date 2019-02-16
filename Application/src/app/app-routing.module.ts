import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { AuthentificationGuard } from './authentification.guard';

const routes: Routes = [
  { path: '', loadChildren: './pages/login/login.module#LoginPageModule' },
  { path: 'login', loadChildren: './pages/login/login.module#LoginPageModule' },
  { path: 'app', loadChildren: './pages/tabs/tabs.module#TabsPageModule', canActivate: [AuthentificationGuard], },
  { path: 'chat/:id', loadChildren: './pages/chat/chat.module#ChatPageModule', canActivate: [AuthentificationGuard], },
  { path: 'user/details/:id', loadChildren: './pages/user-details/user-details.module#UserDetailsPageModule', canActivate: [AuthentificationGuard], },
];
@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule {}
