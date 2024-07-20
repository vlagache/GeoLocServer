# <p align="center">GeoLocServer</p>

<div align="center">
  <img src="/assets/geoloc_logo.png" alt="logo" style="height: 60px;"/>
</div>

## Context

In 2018, I knew nothing about computer development and started [a training course to learn web developer](https://openclassrooms.com/fr/paths/48-developpeur-web-junior). There were several projects to hand in and defend in front of a jury. This was the final project that was supposed to show everything I had learned during the course.


## Project

The subject of the project was entirely open-ended, the only constraint being that I had to use the languages I'd learned during the course: PHP, Javascript, SQL, HTML and CSS.

I made a rather daring choice, given my level of knowledge at the time, to try and develop a mobile application. I used [Symfony](https://symfony.com/) for the server side and [Apache Cordova](https://cordova.apache.org/) for [the mobile application side](https://github.com/vlagache/GeoLocApp).

My idea was to create a mobile application to warn friends and family in the event of a problem during a sports outing. In the event of an alert, the person's position is communicated to their loved ones.

### Technical architecture


<div align="center">
  <img src="/assets/geoloc_schema.png" alt="logo"/>
</div>


### 5 years later.

This code had been sleeping on my github for 5 years and I recently got the urge to clean up the README. It allows me to show the work I was doing at the time and also to realize how far I've come. I've done this for all my old projects, trying to restart them to make a nice presentation, captures, videos.  

This one was special.

On the one hand, I had to reboot a Symfony server in version 4.3, php 7.1.3 and a whole bunch of old dependencies, and on the other, an Apache Cordova application based on Android version 9. Against all odds, I managed to get the server part working almost identically thanks to Docker ❤️ [^1] but I never managed to rebuild the Cordova application. I think there would have been a final problem with Firebase, which I was using to send push notifications to users mobiles: I imagine that the versions of the libraries I was using at the time no longer allow interaction with the current Firebase.

After a week's trial, I've decided to stop: It was too much effort for screenshots 🤯.  
The screenshots below are taken from the presentation I made for the final exam of my training course.

 ### Technologies
 
![Technologies ](https://skillicons.dev/icons?i=symfony,js,mysql,html,css,cordova)

### Deployment

⚠️ **This is the old documentation I wrote in 2019.**

<details>
  <summary>Click to expand !</summary>
  
  ```
  git clone https://github.com/vlagache/GeoLocServer.git
  ```
  Install composer : https://getcomposer.org/download/ 

  ```
  composer install
  ```
  create a config.ini file at the root of your project and put in it

  ```
  apiKeyGoogle = your key to the Google Geocoding Api
  ```
  to get a Google api key: https://developers.google.com/maps/documentation/javascript/geocoding#ReverseGeocoding

  Obtain a private key for Firebase services in JSON format (https://firebase.google.com/docs/admin/setup) and
  put this .json file at the root of your project

  In the .env file

  ```
  DATABASE_URL= your database
  GOOGLE_APPLICATION_CREDENTIALS='../name of json file for firebase services'.
  ```
</details>

 ## My work

> The screenshots were taken from the video that the jury had made of my final presentation. The resolution wasn't crazy, so I tried to improve it with the software [Upscayl](https://upscayl.org/)

<p align="center">
  <img src="/assets/geoloc_first.png" alt="first" width="200"/>
  <img src="/assets/geoloc_connection.png" alt="connection" width="200"/>
  <img src="/assets/geoloc_main.png" alt="main page" width="200"/>
  <img src="/assets/geoloc_account.png" alt="account" width="200"/>
</p>

<p align="center">
  <img src="/assets/geoloc_team.png" alt="team" width="200"/>
  <img src="/assets/geoloc_activity_start.png" alt="start" width="200"/>
  <img src="/assets/geoloc_activity_pause.png" alt="pause" width="200"/>
  <img src="/assets/geoloc_activity_stop.png" alt="stop" width="200"/>
</p>

<p align="center">
  <img src="/assets/geoloc_notification_activity.png" alt="notification activity" width="200"/>
  <img src="/assets/geoloc_alert.png" alt="alert" width="200"/>
  <img src="/assets/geoloc_stop_activity_nofication.png" alt="notification stop" width="200"/>
  <img src="/assets/geoloc_notification_push.png" alt="notification push" width="200"/>
</p>


[^1]: See the [docker-compose.yml](https://github.com/vlagache/GeoLocServer/blob/rebirth/docker-compose.yml) and [Dockerfile](https://github.com/vlagache/GeoLocServer/blob/rebirth/Dockerfile) on [rebirth](https://github.com/vlagache/GeoLocServer/tree/rebirth) branch. With a branch name like that, I really thought it would work... 😭.