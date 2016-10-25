REST Bookmarks
==========

Provides ability to save bookmarks and add comments to them.

Requirements
---
* PHP needs to be a minimum version of PHP 5.3.9
* JSON needs to be enabled
* MySQL (latest stable version)
* Composer Package Manager [Install Composer](http://getcomposer.org/doc/00-intro.md)

Installation
---

```
 $ git clone https://github.com/AnatolyUd/Requests-API.git 
 $ composer install
```
Edit your database parameters in app/config/parameters.yml 

~~~
 $ php app/console doctrine:database:create
 $ php app/console doctrine:schema:create
~~~

Usage
---
Get last 10 Bookmarks

```
 GET http://<YOUR_SITE>/bookmarks
 
 JSON returns:
 
 [{"uid":"<value>", "createdAt":{"date":"<value>"}, "url":"<value>"}, ...]
 
```

Get Bookmark with comments by Bookmark.Url

```
 GET http://<YOUR_SITE>/bookmarks?url=<URL>

 JSON returns:
 
 [{"uid":"<value>", "createdAt":{"date":"<value>"}, "url":"<value>", "comments": [<comments>]}, ...]

```

Add Bookmark by Uid, if it exists then return its Uid;

```
POST http://<YOUR_SITE>/bookmarks

parameters:
"url": "<value>"

JSON returns:

    - on success
{'Success': true, 'uid' => "<UID>"} 

    - on error
{'Success': false, 'Message' => "<Message>"} 

```

Add Comment to Bookmark by Bookmark.Uid and get Comment.Uid

``` 
POST http://<YOUR_SITE>/comments

parameters:
"bookmark_uid": "<value>"
"text": "<value>" 

JSON returns:

    - on success
{'Success': true, 'uid' => "<UID>"} 

    - on error
{'Success': false, 'Message' => "<Message>"} 

```

Update Comment.Text by Uid if it was added after then hour ago and with the same ip address 

```
PUT http://<YOUR_SITE>/comments

parameters:
"uid": "<value>"
"text": "<value>" 

JSON returns:

    - on success
{'Success': true} 

    - on error
{'Success': false, 'Message'} 

```

Remove Comment by Uid if it was added after then hour ago and with the same ip address 

```
DELETE http://<YOUR_SITE>/comments

parameters:
"uid": "<value>"

JSON returns:

    - on success
{'Success': true} 

    - on error
{'Success': false} 

```
