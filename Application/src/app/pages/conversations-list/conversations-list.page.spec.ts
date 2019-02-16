import { CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ConversationsListPage } from './conversations-list.page';

describe('ConversationsListPage', () => {
  let component: ConversationsListPage;
  let fixture: ComponentFixture<ConversationsListPage>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ConversationsListPage ],
      schemas: [CUSTOM_ELEMENTS_SCHEMA],
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ConversationsListPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
