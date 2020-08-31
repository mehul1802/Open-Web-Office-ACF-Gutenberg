=== Open Web Office ACF Gutenberg Theme === 
Tested up to: 5.4.2 
Stable tag: 1.2 
License: GPLv2 or later License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Our default theme for ACF Pro Flexible Content layouts is designed to take full advantage of the flexibility of the ACF Pro Flexible content fields. Organizations and businesses have the ability to create dynamic landing pages with endless layouts using the group and column flexible fields. This starter theme contains normal template to create default page, flexible template to create dynamic layouts.

== Copyright ==

1. This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later version.

2. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

== Instructions ==

	Ø Install Wordpress Installtion of WordPress from WordPress.ordg is pretiy straigh forword. You can follow the steps mentioned here in local or hosting server. Please go thorugh the steps here: https://wordpress.org/support/article/how-to-install-wordpress/

	Ø Installing a WordPress ACF Flexible Starter theme In WordPress add and active any theme by below two steps:

	Ø Download and unzip the theme package from my GitHub URL:

	Ø Log in to your WordPress Dashboard (i.e.: examplesite.com/wp-admin or localhost/wp-admin)

	Ø Click on Appearance > Themes

	Ø On the Themes page, click the Add New button on the top of the page

	Ø Click the Upload Theme button

	Ø Choose the [themename].zip from your theme package download from my GitHub URL.

	Ø Press the Install Now button

	Ø Back on the Themes page, click on Activate

	Or you can pull my theme in Github to the wp-content/themes using GIT commands.

3. ACF Pro plugin required. As this starter theme created to take leverege ACF Flexible content field layouts ACF Pro plugin required to activate the theme. You need to install the ACF Pro plugin then only this starter ACF Flexible theme will activate. If you do not have plugin then it will show error as shown below image:

4. Basic installation As theme activate you can finc ACF-json folder in which one basic import setting json file presnt. You need to import this json file in ACF > Tools > import This json will add two filed groups one for theme options as Header & Footer & Custom post type module. Second field group is ACF Flexible content group where you can register layouts. This field group only assign to the Home flex and page flex template only.

5. Get started from stater theme Once you all set you just need to create a Gutenberb blocks and add same to the page or post. If this layout is new then on page load it will automatically create a block template in theme > template-parts > acf-blocks folder with basic text as "Add HTML here". On this block template you can add your HTML and ACF sub field dynamic php code so it will show the layout on the page. This block also create CSS file in assests > css > acf-blocks-css and same in JS file in assests > js > acf-blocks-js folder. This JS and CSS will be specific to the individual block.

6. Benefit of this starter theme as this ACF gutenberg block templates created dynamically so developer or designer jsut need to work on those block templates without doing any extra page templates. All gutenberg blocks are become global so admin can set any block in any page so there will benefit to create layouts in quick time.

7. As this starter theme doesnt have any page builder and once developer start to develop any website then from the start developer have upper hand to develop high performance website.

8. Now how to register ACF blocks? 
To create ACF Gutenberg blocks you need perform these below steps:
- Go to ACF creat new field group
- Name field group as "Block: Name of the block", example for testimonial block it should be like this: "Block: Testimonial"
remember you must need to keep this format name as stated above.
- Once you create a field group you need to go below Settings area for "Gutenberg Block" and make it on to convert this field group to ACF Block.
- There are other options to choose like icon for block, enable block for specific post or page, alighment option on or off.
- Add field to the block fields group.
- Add this block in any page and post, once it published it will automatically create respective PHP, CSS and JS files.
- You just need work on those files to make section as you want and its done.


Extra addon : Custom post type As we already taking leverage on ACF Pro plugin so, there will be an theme option on which you can register the custom post type and custom taxonomy to create dyanamic post listing and detail post other then defaul post type.
In theme option you just need to pass custom post type name and select below options as show below image. We have given option if you deselect the option then no additional code will load on theme.

== Dependencies ==

ACF pro WordPress plugin
WP folder should have full rights to create automatic layouts php file in theme folder.