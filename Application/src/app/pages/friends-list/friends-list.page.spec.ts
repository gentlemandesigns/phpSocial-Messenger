import { CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { FriendsListPage } from './friends-list.page';

describe('FriendsListPage', () => {
  let component: FriendsListPage;
  let fixture: ComponentFixture<FriendsListPage>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ FriendsListPage ],
      schemas: [CUSTOM_ELEMENTS_SCHEMA],
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(FriendsListPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
