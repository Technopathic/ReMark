<?php

use Illuminate\Database\Seeder;

class MtopicsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      $date = date('Y-m-d H:i:s');

      DB::table('mtopics')->insert([
        'id' => 1,
        'topicSlug' => 'Welcome-to-ReMark',
        'topicTitle' => 'Welcome to ReMark',
        'topicBody' => "Hi there!\r\n\r\n## Welcome to ReMark!  \r\n\r\nReMark is a blogging and publishing platform which allows you to create blog posts, podcasts, and vlogs quickly and easily. ReMark also comes with a great Dashboard where you can view the latest updates from your favorite websites around the net.  \r\n\r\nIsn't that neat?\r\n\r\nI've been working on ReMark for the past 7 months or so. I'm really excited have finally released it. I hope you enjoy ReMark as much as you can. It's still under heavy development and I'm trying to do as many features and bug-fixes as possible. If you have any questions, concerns, or requests, feel free to submit them on my Github or post them down below.  \r\n\r\nStay tuned, there's a lot more to come.",
        'topicImg' => 'http://h4z.it/Image/9a0968_ReWall-One.png',
        'topicAudio' => 0,
        'topicVideo' => 0,
        'topicThumbnail' => 'http://h4z.it/Image/11a656_ll-One-Thumb.png',
        'topicChannel' => 2,
        'topicViews' => 0,
        'topicReplies' => 0,
        'topicAuthor' => 1,
        'topicStatus' => 'Published',
        'topicArchived' => 0,
        'topicFeature' => 1,
        'topicTags' => NULL,
        'topicVotes' => 0,
        'topicType' => 'Blog',
        'pageMenu' => 1,
        'allowReplies' => 1,
        'created_at' => $date,
        'updated_at' => $date
      ]);

      DB::table('mtopics')->insert([
        'id' => 2,
        'topicSlug' => 'ReMark-Quick-Start',
        'topicTitle' => 'ReMark: Quick Start',
        'topicBody' => "### Production\r\n\r\nThis guide is to help you get up and running with a production version of ReMark for the web as soon as possible.  \r\n\r\n### Requirements\r\n\r\n*   An HTTP Web Server (Apache or Nginx)\r\n*   MySQL\r\n*   PHP 5.6+\r\n*   Composer\r\n*   NPM with Bower  \r\n\r\n### Configuration\r\n\r\nReMark needs a few PHP modules enabled before it can function correctly. Please enable Fileinfo, Exif, GD, and mcrypt. You should already have the rest of the necessary modules in a default PHP installation.  \r\nNext, edit your php.ini to allow a \"post_max_size\" and \"upload_max_size\" of at least 10M. Be sure your cgi.fix_pathinfo is set to 0\\. Restart your PHP processor.  \r\n\r\nBe sure to create a MySQL database for ReMark.  \r\n\r\n### Installing ReMark\r\n\r\nDownload ReMark from the Official Github either by cloning the Repo or Downloading the ZIP archive onto your server's public directory. We will need to set permissions for ReMark. In the root ReMark folder run:\r\n\r\n<pre><span class=\"rangySelectionBoundary\" id=\"selectionBoundary_1467203455578_5708485874802086\">﻿</span>chmod 0777 -R storage  \r\nchmod 0777 -R public/storage  \r\nchmod 0666 .env  \r\nchmod 0666 app/Http/routes.php<span class=\"rangySelectionBoundary\" id=\"selectionBoundary_1467203455577_9500871634745977\">﻿</span>  \r\n</pre>\r\n\r\nNow we will download the necessary PHP/Laravel packages and Javascript packages for ReMark.  \r\n\r\n<pre>composer install</pre>\r\n\r\n<pre>bower install  \r\n</pre>\r\n\r\nOnce you're done with that, navigate yourself to \"http://yourwebsite/install\" and follow through with the ReMark installer. Add your MySQL database, Website Name, and Administrator Information. Click Install and you should receive confirmation that ReMark has been properly installed then redirected to your new ReMark home page.  \r\n\r\nThat should be it! If you have any questions or comments feel free to discuss them on the ReMark github issues page.",
        'topicImg' => 'http://h4z.it/Image/45a725_ReWall-Two.png',
        'topicAudio' => 0,
        'topicVideo' => 0,
        'topicThumbnail' => 'http://h4z.it/Image/b22390_wo-Thumbnail.png',
        'topicChannel' => 4,
        'topicViews' => 0,
        'topicReplies' => 0,
        'topicAuthor' => 1,
        'topicStatus' => 'Published',
        'topicArchived' => 0,
        'topicFeature' => 1,
        'topicTags' => NULL,
        'topicVotes' => 0,
        'topicType' => 'Blog',
        'pageMenu' => 0,
        'allowReplies' => 1,
        'created_at' => $date,
        'updated_at' => $date
      ]);

      DB::table('mtopics')->insert([
        'id' => 3,
        'topicSlug' => 'ReMark-What-Are-Feeds',
        'topicTitle' => 'ReMark: What Are Feeds?',
        'topicBody' => "### Introduction\r\n\r\nI always thought it would be great to have all of my source material in one place so I can freely pull information when I need it. From the Feed Dashboard, you can view all of the latest articles or activity from your favorite blogs or websites. You seen view their sources or bookmark a Feed post to use later.  \r\n\r\n![](http://h4z.it/Image/fb9f7c_odoFeedSmall.PNG)\r\n\r\n### Getting Feeds\r\n\r\nTo add a new feed to your Dashboard, simple go to the Catalogue tab. There you can search and add a particular website you would like to receive updates from. A selected Feed will be added to your Dashboard for to view anytime.  \r\n\r\nTo view the Source Code of these feeds, go to the [ReMarks-Feed Github page](https://github.com/Technopathic/ReMark-Feeds) and click on the \"master\" branch pull-down. From there you can navigate across other feeds which have already been made.\r\n\r\n![](http://h4z.it/Image/4fe277_eenshotSmall.PNG)\r\n\r\n### Creating Feeds\r\n\r\nFeeds are a fairly new feature, so chances are you will not find a website you want listed in the catalogue. Therefore, you have a few choices.\r\n\r\nThe first choice is to submit a Feed request on the Github page here:\r\n\r\nIf creating the Feed is feasible, you'll receive an email stating that your requested Feed was created.The second choice involves creating the Feed yourself, which is quite easy.  \r\n\r\nIt should be noted that there are two types of Feeds that can be made, depending on the website you would like. The first kind are Normal Feeds, which are websites that scraped by ReMark with the content delivered to your dashboard. The second types are API Feeds, which are websites that cannot be scraped and rely heavily on Javascript (example: Twitch.tv).\r\n\r\n### Normal Feeds  \r\n\r\nLet's get started with Normal Feeds.\r\n\r\nStep 1: Download the Feed Template: [https://github.com/Technopathic/ReMark-Feeds/archive/Feed-Template.zip](https://github.com/Technopathic/ReMark-Feeds/archive/Feed-Template.zip)  \r\n\r\nStep 2: Navigate to the website and the area you would like to turn into a Feed.\r\n\r\nStep 3: Open Firefox's Inspector or Chrome's Dev Tools (Ctrl+Shift+C).\r\n\r\nStep 4: Let's start with the \"info\" section. This is the website's general information.The \"icon\" is referring to the website's logo. You will have to find an image link yourself.The \"source\" is the exact webpage that will be picked up by ReMark's feed system.\r\n\r\nStep 5: Next is the \"feed\" section. In the Inspector, find the containing one of your website's latest posts and add that Class or Id as the \"Container\". Next, find the element that holds a post's title and add that in \"title\" of your feed.\"media\" refers to any thumbnail that might be attached to that post (This is optional). At this moment, ReMark only supports Images in the Media field.\"mediaSrc\" is for special cases when the \"media\" uses a different element aside from \"src\" (such as \"data-srcset\", \"source\", or etc) (This is also optional).\"link\" is the link to the full article. (Usually an 'a href' tag).\r\n\r\nStep 6: The options section can be left empty if necessary. The Options section can contain two fields: \"prependMedia\" and \"prependLinks\". In certain cases, links and Media may not have their domains attached to them (example: /img/file.jpg), this can be a problem when displaying in the ReMark Feed system. So, to help fix this, simply add the domain of the website you're making a Feed of to prependMedia and/or prependLinks.\r\n\r\nThat's all for Normal Feeds.\r\n\r\n### API Feeds  \r\n\r\nNext we will look at API Feeds. API Feeds are similar to Normal Feeds, but API feeds get its data from a Webapp's JSON API.  \r\n\r\nFollow the above Steps 1 through Step 4\\. In the \"info\" section, API feeds take two more extra fields, \"type\" and \"api\". The \"type\" field will be set to \"api\" and the \"api\" field will be set to the JSON API URL of that website. Don't worry, that can sound confusing at first.  \r\n\r\nThe most difficult part may just be getting the JSON feed from the source website. Some webapps/websites won't explicitly tell you where their public APIs are, so you have to go looking for them yourself. In the case of the Twitch.tv feed, I opened the Firefox/Chrome Console (Ctrl+Shift+K) and reloaded the page and did a quick look around for an API URL I could use to acquire the Latest Featured Streams. So if the Webapp you would like to Scrape does not have any documentation on their API, there's always that option, but if the Webapp has documentation of their API, give that a look through and stick with it.  \r\n\r\nMoving on to the ReMark Feeds \"feed\" section of the file. The API Feeds syntax is slightly different from the Normal Feeds. The \"container\" key will hold the API key of the array containing the posts you want from the Website's JSON API. As for the rest of the ReMark Feed keys, they must all be properly nested and each API key must be separated by a comma and a space (Example: \", \"). So for example: \"title\": \"latest, post, title\".\r\n\r\nStep 6 above regarding the Options also applies to API Feeds.  \r\n\r\nAfter you've created your Feeds, you can submit your feed on the ReMarks-Feed Github page. Your Feed will be looked over and tested. If any adjustments need to be made, you'll be notified. If your Feed is approved, you will be notified. As I've mentioned, this is fairly new and experimental in ReMark, so extreme cases may even involve add new commits to the ReMark source code itself.If you have any questions or concerns, feel free to post them on the [ReMark-Feeds issues page](https://github.com/Technopathic/ReMark-Feeds/issues). Looking for some of the Examples of already made Feeds might also help.\r\n\r\n**Happy Blogging!**",
        'topicImg' => 'http://h4z.it/Image/bfc7f1_ReWall-Four.png',
        'topicAudio' => 0,
        'topicVideo' => 0,
        'topicThumbnail' => 'http://h4z.it/Image/82f4e8_ur-Thumbnail.png',
        'topicChannel' => 4,
        'topicViews' => 0,
        'topicReplies' => 0,
        'topicAuthor' => 1,
        'topicStatus' => 'Published',
        'topicArchived' => 0,
        'topicFeature' => 0,
        'topicTags' => NULL,
        'topicVotes' => 0,
        'topicType' => 'Blog',
        'pageMenu' => 0,
        'allowReplies' => 1,
        'created_at' => $date,
        'updated_at' => $date
      ]);

      DB::table('mtopics')->insert([
        'id' => 4,
        'topicSlug' => 'Audio-Podcasts',
        'topicTitle' => 'Audio Podcasts',
        'topicBody' => "### Audio Postcasting\r\n\r\nPodcasting has been a hobby of mine for the past few years. There was just something about being able to vent through a mic, as if I was talking to another person, that made articulating my thoughts and opinions easier.  \r\n\r\nWhen I introduced ReMark to my friends and acquaintances, some were quite drawn to the idea of being able to make Podcasts on the fly with ReMark. One such acquaintance had said something in particular which greatly caught my attention.  \r\n\r\n> \"I have all of these ideas, but for me to sit down in front of a computer and write about them on a blog? I just can't do it.\"\r\n\r\nFor some reason, this really stuck to me, mainly because it is true. Everybody has ideas they would just love to share, but not everybody has the tenacity or drive to sit down and put their thoughts into written, or typed, words.\r\n\r\nIncluding the ability to Record audio directly in ReMark was an important step for me. It allowed me to give those people another medium to express themselves. Additionally, given that the Audio recording feature doesn't include any fancy editors or effects, it leaves just the raw projection of who the blogger really is, which is exactly what I wanted, to bring back the idea of extremely personal blogging.  \r\n\r\nAudio blogging can be done very easily in ReMark by starting a new topic, and clicking on the \"Audio\" tab. There you can upload your own Audio files or record your own Audio in ReMark by clicking on the Mic icon.\r\n\r\n![](http://h4z.it/Image/c7da22_Capture.PNG)\r\n\r\nAnd away you go!",
        'topicImg' => 0,
        'topicAudio' => 0,
        'topicVideo' => 0,
        'topicThumbnail' => 0,
        'topicChannel' => 3,
        'topicViews' => 0,
        'topicReplies' => 0,
        'topicAuthor' => 1,
        'topicStatus' => 'Published',
        'topicArchived' => 0,
        'topicFeature' => 0,
        'topicTags' => NULL,
        'topicVotes' => 0,
        'topicType' => 'Blog',
        'pageMenu' => 0,
        'allowReplies' => 1,
        'created_at' => $date,
        'updated_at' => $date
      ]);

      DB::table('mtopics')->insert([
        'id' => 5,
        'topicSlug' => 'ReMark-Vlogs',
        'topicTitle' => 'ReMark Vlogs',
        'topicBody' => "### What are Video Topics?\r\n\r\nOne of my goals for ReMark was to give he user a wide array of methods with which they can create content. Right up there with Audio Podcasting, Video blogs have also gained popularity in the past few years. Just like with Audio posts, not everyone has the drive to sit down in front of a computer and type out their thoughts or ideas. Then there are concepts which you feel the need to give a visual representation. Maybe a street performer you saw downtown, or a concert you're attending. Whatever the reason, Video blogging can help you show the world what you want them to see.  \r\n\r\n### How do I make a Video Topic?\r\n\r\nTo make a Video Topic, you simply have to create a new Topic on the Admin Dashboard and select the \"Video\" tab. Be sure that your Microphone and Camera are plugged in to your computer. Then click on the Recording Icon and start blogging!\r\n\r\nAdditionally, you can also upload a previous video on ReMark. Currently, ReMark supports only .OGG video files, so I recommend finding an online converter first.  \r\n\r\n**Happy Blogging!**",
        'topicImg' => 0,
        'topicAudio' => 0,
        'topicVideo' => 0,
        'topicThumbnail' => 0,
        'topicChannel' => 3,
        'topicViews' => 0,
        'topicReplies' => 0,
        'topicAuthor' => 1,
        'topicStatus' => 'Published',
        'topicArchived' => 0,
        'topicFeature' => 0,
        'topicTags' => NULL,
        'topicVotes' => 0,
        'topicType' => 'Blog',
        'pageMenu' => 0,
        'allowReplies' => 1,
        'created_at' => $date,
        'updated_at' => $date
      ]);

      DB::table('mtopics')->insert([
        'id' => 6,
        'topicSlug' => 'User-Management',
        'topicTitle' => 'User Management',
        'topicBody' => "### Humble Beginnings\r\n\r\nThis is where it all started really, the first aspect of ReMark that I ever worked on was the User Management System. It's actually how ReMark came about, really, I needed a reusable way to quickly sort out, edit, and ban Users from my previous smaller apps. Little did I know, it would evolve into a full-fledged blogging platform.\r\n\r\n### What does it do?\r\n\r\nThe User Management system does exactly what it implies, it brings greatness to managing your users who have signed up to your ReMark website. From the Users page in the Dashboard, you can sort out all of your members, assign roles, as well as edit, ban, and delete members. Here you can also manually activate a user or reset their passwords if necessary.\r\n\r\n### Roles  \r\n\r\n\r\nRoles are permissive titles assigned users, such as \"Administrator\" or \"Members\". The owner of a ReMark website is automatically set as an Administrator and anybody else who signs up is set as a Member. ReMark allows you to create more roles if you would like and to have some fun with it for your members. You can create a role such as a \"Neophyte\" to represent new members or create the role \"Guru\" for veterans.\r\n\r\n### Subscribers  \r\n\r\n\r\nThe User Management system is also where you can manage your Subscribers who are not members of your ReMark website, but have opted to receive a weekly digest of Topics you have created.",
        'topicImg' => 0,
        'topicAudio' => 0,
        'topicVideo' => 0,
        'topicThumbnail' => 0,
        'topicChannel' => 3,
        'topicViews' => 0,
        'topicReplies' => 0,
        'topicAuthor' => 1,
        'topicStatus' => 'Published',
        'topicArchived' => 0,
        'topicFeature' => 0,
        'topicTags' => NULL,
        'topicVotes' => 0,
        'topicType' => 'Blog',
        'pageMenu' => 0,
        'allowReplies' => 1,
        'created_at' => $date,
        'updated_at' => $date
      ]);

      DB::table('mtopics')->insert([
        'id' => 7,
        'topicSlug' => 'ReMark-Apps',
        'topicTitle' => 'ReMark Apps',
        'topicBody' => "### What are Apps?\r\n\r\nI spent a lot of time mulling over how I wanted to allow users to enhance ReMark with their own customization. I could not expect most average users to be very fluent in HTML5 or CSS3, so I would need to make an easier way for people to install their own Themes or Mods. As it would turn out, there's a fine line between Themes and Mods, I figured the best idea was to combine them into \"Apps\".\r\n\r\n\r\nAn \"App\" is basically a Front-end for your website, it can contain both of an overall look for your ReMark website AND additional Features that may enhance your or your members' experience. If you're wondering why I combined the two, the answer is simple. I saw examples of other platforms becoming insecure and incompatible with each other the more \"plugins\" a user would install. A blog/website should have a clearly defined singular purpose and target audience, there's no need to include extraneous features.\r\n\r\n### How do I create Apps?\r\n\r\nCreating a App is really easy and in most cases, doesn't even require a lot of HTML5/CSS3 experience. Start off by downloading the [ReMark App starter template](https://github.com/Technopathic/ReMark-Starter-App) Once you have the template, we'll go over the folders inside the template.\r\n\r\n\r\n**For Beginners:**  \r\nYou'll notice some familiar folders if you've ever taken a web design class. The CSS folder will contain all of your stylesheets. The JS folder will contain all of your Javascript functions (written in an AngularJS controller). The Img folder holds any additional images you would like to include for your App. The Fonts folder holds additional fonts. The Libs folder will contains any additional libraries you want to include, such as Jquery. Lastly, the Views folder holds you HTML files that should display any whole pages or fragments. You must include any additional files added to the project in the index.html as you would a standard HTML website (with the usual script and link tags).\r\n\r\n\r\n**For Advanced:**  \r\n If you're familiar with PHP and Laravel, you can add Server-Sided functions, Models, and Database table to the Controllers, Models, and Migrations folder. You can then tie your server functions together into the Routes file.\r\n\r\n\r\nI recommend Designing your App as a normal website first, in plain HTML5/CSS and Javascript. Then you can easily port it over to a ReMark App. If you're not familiar with AngularJS, you can actually just take your Javascript functions and move them into the template file found the JS folder. Put your functions inside of the Controller's { } curly brackets. Porting the CSS, Imgs, and Libs is as easy as moving your files over to the template folder. Adding new Views can be a little trickier. Full page views can be put in the Views folder, but Fragments (such as a dialog box or bottom sheet) must be put in the \"templates\" folder within the Views folder. Okay, it's actually not that tricky.\r\n\r\n\r\nThe last and most important piece of creating a new ReMark app, is the app.js. This file contains information related to your new theme. It's pretty straight forward, so you can edit it accordingly. Fill in the App name, Author, Version, a Description, link to a preview image, you can keep the Framework as AngularJS if you're not sure what this means, and a link to any documentation of your App.\r\n\r\n\r\nOnce that is done, you can ZIP up the App and head on over to your Dashboard's Options page. Scroll down a bit and you'll see an Add New App button. Upload your App and Activate it. If all goes well, you should have successfully finished creating your own ReMark App. If all does not go well, do not worry, ReMark automatically creates a new backup of the previous App every time a new App is activated.\r\n\r\nI also recommend reading this Guide on Directives as well: [ReMark Directives Guide](https://github.com/Technopathic/ReMark/wiki/Directives)",
        'topicImg' => 'http://h4z.it/Image/849b36_ReWall-Three.png',
        'topicAudio' => 0,
        'topicVideo' => 0,
        'topicThumbnail' => 'http://h4z.it/Image/42a9e9_ee-Thumbnail.png',
        'topicChannel' => 4,
        'topicViews' => 0,
        'topicReplies' => 0,
        'topicAuthor' => 1,
        'topicStatus' => 'Published',
        'topicArchived' => 0,
        'topicFeature' => 0,
        'topicTags' => NULL,
        'topicVotes' => 0,
        'topicType' => 'Blog',
        'pageMenu' => 0,
        'allowReplies' => 1,
        'created_at' => $date,
        'updated_at' => $date
      ]);

      DB::table('mtopics')->insert([
        'id' => 8,
        'topicSlug' => 'Road-to-ReMark-Chapter-One',
        'topicTitle' => 'Road to ReMark: Chapter One',
        'topicBody' => "##Road to ReMark: Chapter 1\r\n\r\nIt's been an eventful several months working on ReMark and I've learned a lot. I've decided to detail some significant points during ReMark's development and hope that whoever is reading this might find it interesting or helpful in any way.\r\n\r\nTo start us off, I'd like to talk about one of the largest aspect of ReMark, the Feed System. All was not sunny and shining when I developed the Feed System, no Ser. I wanted to give my users a place where they could have all of their information on one dashboard. If anybody remembers \"Google Dashboard\", I can say that was my biggest inspiration for the ReMark Feed Dashboard. Google Dashboard was one of Google's better projects, before Google Plus. You could add RSS Feeds to your personalized Google Dashboard and it would give you a nice list of the latest posts in that RSS Feed. Unfortunately, Google Dashboard was closed down and replaced with whatever rebranded \"Google Dashboard\" they have right now. Although, this is rightfully so, because we've also seen Google depreciate their RSS Feed API, which would have been a huge loss for me, if I was not forewarned.\r\n\r\nThe first iteration of ReMark's Dashboard was written in Laravel, without AngularJS at all. The Feed System was dependent on Google's RSS Feed API (Depreciated), which turned out to be a huge problem. Oh sure, it worked then, for maybe a few small websites, but when it came to getting feeds from more than 3 websites, it would crap out. The API was sinfully slow and as it would happen, some websites didn't even support RSS anymore, such as Youtube. So I had to come up with an alternative.\r\nIt was around this time I had decided to rewrite the ReMark Dashboard using AngularJS. I did not want Laravel to produce any client-side views, just an API. At this moment, it struck me, \"Why not just create my own Feed System?\" Seemed like a good idea. Out with XML, in with JSON. I started off with creating a basic format of how I wanted the Data to be represented, based off of Google's Feed API. I needed the \"title\", \"published date\", \"author\", \"excerpt\", \"media\", and a \"permalink\". Coming up with the format was simple enough, but next came the hard part... Acquiring the data.\r\n\r\nI don't exactly remember when or why I was introduced to it, but I remember taking a look at Goutte, believing web scraping to be the answer. Goutte is a PHP package that uses Symphony's DomCrawler and CSSselector libraries to scrape other websites. After a lot of trial and error, I managed to create a decent function for my Feed format. The function would iterate through a certain section of a website containing the specified content of specified elements, then format it in an appropriate way to be displayed on ReMark. This worked fantastically for a few examples. But then I suddenly ran into a few issues.\r\n\r\nThe biggest caveat for my Feed system was Javascript. That's right, because unless there's a prerender of the choice website, only static content can be scraped. The biggest perpetrator of my JavaScript woes were Lazy Load Images. Since Lazy Load Images load onto the DOM after you scroll into them, they are not static, and therefore cannot be picked by the scraper. But all was not lost. Thankfully! Some websites were courteous enough to adhere to those who do not have JavaScript enabled, as you should. They kept the static image link in a \"data-original\" or \"data-src\" attribute in the Lazy Load img tag. This is why you'll notice, on the ReMark Feeds Format, I have a \"mediaSrc\" field, specifically for this problem. It just specifies the correct attribute to pull the Image link from. Hurray! Granted, I still run into problems when the choice website does not have the original image link (Looking at you, GQ). Now this was fine for getting a Media cover image, but unfortunately, I could not specify the img attribute when getting excerpt content. Which is why one of my favorite features of the Feed System had to be removed. Originally, the Feed System also had the ability to grab Single posts of Feeds. The Content would then be formatted to look like a ReMark blog post and all advertisements would be removed. This was to make viewing other Feed posts a lot easier and to give my users a way to quickly blog about source material. Sadly, formatting other website HTML content was not easily done. Lazy Loaded images would show up as blank white spaces, and some websites added strange elements to their content (such as a ridiculous amount of \"\\t\" and \"\\n\" for whatever reason). So, I ripped that feature out and linked the source material instead. I'm sure website owners would be happier with this decision as well.\r\n\r\nI hope to maybe one day move away from Scraping websites for Feed content, which is why I quickly implemented a different type of Feed into the Feed system a lot sooner than I expected. When I attempted to create a Feed for Twitch.tv, I was unable to acquire any data. After disabling JavaScript on my browser, I noticed that Twitch.tv didn't load past their logo. Of course it won't! Twitch.tv is an EmberJS application! It's all JavaScript! Nobody without JavaScript would browse Twitch.tv! Why would anybody with JS disabled browse a Video Streaming website?! Alright, calm down... Maybe forget about creating a Feed for Twitch's main website. How about their official blog instead? Sure, but it's boring. So I browsed Twitch's Official API and managed to find a link to the \"Latest Featured Streams\". I figured, \"Hey! I know all about API and JSON! I can use this!\" and I did. Although, I will tell you, creating the function to format another JSON API to suit my JSON API was not a walk in the park, but somehow I managed to pull through. Thus another crisis averted.\r\n\r\nMaybe a lot of this might have been easier for someone else, but I'm learning now so one day I can look back on all of this and realize how much effort I put into making it easier for myself.",
        'topicImg' => 'http://h4z.it/Image/a46c72_ReWall-Five.png',
        'topicAudio' => 0,
        'topicVideo' => 0,
        'topicThumbnail' => 'http://h4z.it/Image/ebaa1e_ve-Thumbnail.png',
        'topicChannel' => 2,
        'topicViews' => 0,
        'topicReplies' => 0,
        'topicAuthor' => 1,
        'topicStatus' => 'Published',
        'topicArchived' => 0,
        'topicFeature' => 0,
        'topicTags' => NULL,
        'topicVotes' => 0,
        'topicType' => 'Blog',
        'pageMenu' => 0,
        'allowReplies' => 1,
        'created_at' => $date,
        'updated_at' => $date
      ]);

    }
}
