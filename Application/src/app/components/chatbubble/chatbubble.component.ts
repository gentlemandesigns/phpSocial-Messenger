import { Component, OnInit } from '@angular/core';
import { PhotoViewer } from '@ionic-native/photo-viewer/ngx';

@Component({
  selector: 'app-chatbubble',
  templateUrl: './chatbubble.component.html',
  styleUrls: ['./chatbubble.component.scss'],
  inputs: ['message', 'position']
})
export class ChatbubbleComponent implements OnInit {

  message: any;
  position: string;
  timeVisible: boolean = false;

  constructor(private photoViewer: PhotoViewer) {

  }

  ngOnInit() {
    
  }

  toggleTime(){
    this.timeVisible = !this.timeVisible;
  }

  showPhoto(){
    this.photoViewer.show(this.message.message_value);
  }

}
