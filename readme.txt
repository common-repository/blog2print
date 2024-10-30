=== Plugin Name ===
Contributors: Kai-Ingo Neumann
Donate link: http://www.1buch.com/de/blog2print/
Tags: print, book, pdf, print-on-demand
Requires at least: 2.1
Tested up to: 2.7.1
Stable tag: 0.6

Create a book based on your weblog - download it as PDF (for free) or order it as real book at 1buch.com

== Description ==

This plugin provides the possibility to create a book based on your weblog. You define a period to select some weblog 
items or take all your posts. This plugin generates xml-data of your posts and sends this xml to the 1buch.com server 
to format your weblog as book, including a table of contents and pagination.
You can download the generated PDF (for free).

== Installation ==

1. Upload the blog2print folder to the /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

= Do I need a PDF library on my server? =

No - your data will be formatted on the 1buch.com server

= What format has the generated PDF?  =

At the moment the book is designed for the A5 format (140mm × 210mm)

= Can I change the date format presented in the book? =

Yes, you can edit the blog2print_1buch_com/config.ini file to change the date format. 
The date is formatted with the PHP function strftime() - so you can use all options this function offers.


== Screenshots ==

1. You define the title and the author for your book and select the date range.  -> screenshot-1.png
2. The blog2print plugin selects the item to be assembled in your book. In the background it creates xml data – 
these xml data will be sent to the 1buch.com server to be formatted there as book pdf with pagination, justification 
and table of contents.
-> screenshot-2.png
3. On the 1buch.com server the data will be formatted as book pdf file ready for printing. You can download the generated PDF (for free) or can purchase a real book printed by 1buch.com
-> screenshot-3.png


