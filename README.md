Meo Kanal PHP class
===================

A PHP class to handle with the Meo Kanal API

Getting the OAuth keys
======================

You only need to do this once for each application.

Follow these steps to get your OAuth keys:

 * Go to http://kanal.pt/developer/application/register and enter:
    1. Your application name
    2. The application description
    3. The OAuth redirect URI for your app, see below.

 * After your app is approved go here: http://kanal.pt/developer/application/listing
    1. Grab the Client ID and Client Secret and then paste them into the kanal_oauth.php file

 * Place the kanal_oauth.php script (with the keys) in a PHP webserver of yours eg: http://myblog.com/kanal_oauth.php
 * Make sure the OAuth redirect URI for your app at http://kanal.pt/developer/application/listing is the same: http://myblog.com/kanal_oauth.php
 * Now go to http://myblog.com/kanal_oauth.php
 * You'll be redirected to Meo Kanal and asked to authorize the app.
 * When you return, you'll get missing bit of information: your Access Token.

 * Now you have the Client ID, Client Secret and Access Token keys, which you can use with the Meo Kanal class. You don't need the kanal_oauth.php script anymore, nor the OAuth2 classes.

Using the class
===============

It's self explanatory, check the kanal_unit_tests.php script and the Meo Kanal API documentation at http://kanal.pt/developer/console#intro_doc

This is a work in progress