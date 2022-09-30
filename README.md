# PHP presentation system

This repo holds the PHP presentation system hosted on https://talks.php.net.

You can find the presentations under https://github.com/php/presentations.

This system works perfectly for PHP 5.3 and needs the XML extension.

## Files 

Brief walkthrough of how this all hangs together:

config.php            - User configurable paths and filenames
flash.php             - Flash widget rendering embedded from objects.php
fonts/                - Font files
help.php              - Help file
index.php             - Prints list of available presentations
objects.php           - Top-level widget rendering
presentations/        - Directory of presentation files (from separate CVS)
show.php              - Main slide display code
XML_Presentation.php  - XML parser for the presentation files
XML_Slide.php         - XML parser for the slide files

When a user first enters the site index.php presents a list of presentations
by walking through the presentations/ directory and parsing each *.xml file
using the XML_Presentation XML parser.  The filename of the presentation file
without the extension becomes the id by which the presentation is known.  This
string is passed to show.php via PATH_INFO (eg. show.php/intro)

Now we are in show.php.  Here we first parse $PATH_INFO to figure out which
presentation file to read.  Open the presentation file and grab the list of 
slides.  First slide is #0 internally.  Will probably show it to the user as
starting at #1.  The presentation file itself looks like this:

<presentation>
 <title>PHP - Scripting the Web</title>
 <event>OSCON</event>
 <location>San Diego</location>
 <date>July 22, 2002</date>
 <speaker>Rasmus</speaker>
 <navmode>flash</navmode>

 <slide>slides/slide1.xml</slide>
 <slide>slides/slide2.xml</slide>

</presentation>

That is, you start it with a <presentation> tag, and have at least a title
tag inside.  The others are optional.  Then a series of <slide> tags where
you put the relative filename (to the presentations dir) of each slide.

This particular presentation XML file ends up getting parsed into an
array of objects that looks like this:

Array
(
    [1] => _presentation Object
        (
            [title] => PHP - Scripting the Web
            [navmode] => flash
            [event] => OSCON
            [location] => San Diego
            [date] => July 22, 2002
            [speaker] => Rasmus
            [slides] => Array
                (
                    [0] => _pres_slide Object
                        (
                            [filename] => slides/slide1.xml
                        )

                    [1] => _pres_slide Object
                        (
                            [filename] => slides/slide2.xml
                        )

                )

        )

)

Technically you could put more than 1 presentation in the same file, but this
isn't completely supported at this point.  This presentation object gets stored
in $pres which is a session variable.  We will see why in a little bit.  So
to get at the title of the presentation you would use:

  $pres[1]->title

And to get the filename of the first slides you would use:

  $pres[1]->slides[0]->filename

The slide XML files start with a <slide> tag and needs a <title> tag as well.
Then it can have any combination of the following tags:

<blurb>   Paragraph of text which can contain <title> and <text>
<list>    Bullet list which can contain <title> and <bullet>
<image>   Image which can contain <title> and <filename>
<example> Example block which can contain <title>, <text> or <filename>          

See the example slides in the slides/ directory for more info.

If you look in objects.php you will see that each widget type has a $mode
property and that the display() method for each one looks something like this:

    function display() {
        $this->{$this->mode}();
    }

And then you can have html(), flash(), text(), svg(), jpg() methods for each
one.

## Contributing

Have a look at the TODO file for a list of things to start working on.  If
we all pitch in a bit we should all end up with cool-looking presentations
through this very flexible system.

