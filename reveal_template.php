<!doctype html>
<html lang="en">

	<head>
		<meta charset="utf-8">

		<title><?=$title?></title>

		<meta name="description" content="<?=$title?>">
		<meta name="author" content="<?=$speaker?>">

		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui">

		<link rel="stylesheet" href="/reveal.js/css/reveal.css">
		<link rel="stylesheet" href="/reveal.js/css/theme/white.css" id="theme">

		<!-- Printing and PDF exports -->
		<script>
			var link = document.createElement( 'link' );
			link.rel = 'stylesheet';
			link.type = 'text/css';
			link.href = window.location.search.match( /print-pdf/gi ) ? 'css/print/pdf.css' : 'css/print/paper.css';
			document.getElementsByTagName( 'head' )[0].appendChild( link );
		</script>

		<!-- For syntax highlighting - note that these are not the generic highlight.js theme files - see https://github.com/nwinkler/reveal-highlight-themes -->
		<link rel="stylesheet" href="/styles/xcode.css">

		<!-- Override a few styles -->
        <style>
        /*
		   Not actually sure why this block isn't being picked up from the syntax highlight css 
		   If you change the syntax highlight theme, copy the first block here
		*/
        .reveal pre {
                width: 100%;
        }

        .reveal pre code {
                display: block;
                max-height: 600px;
                overflow-x: auto;
                padding: 0.5em;
                line-height: 125%;
                background: #fff;
                color: black;
                -webkit-text-size-adjust: none;
        }

        .reveal section img {
               box-shadow: none;
               border: none;
        }

        .reveal code.shell {
            display: block;
            overflow-x: auto;
            padding: 0.5em;
            background: #000;
            color: #ddd;
            line-height: 125%;
            -webkit-text-size-adjust: none;
        }

        .reveal code.result {
            display: block;
            overflow-x: auto;
            padding: 0.5em;
            background: #ddd;
            color: #000;
            line-height: 125%;
            -webkit-text-size-adjust: none;
        }

        /* Left-align h3 and h4 if they are p elements */
        h3.p {
                text-align: left;
        }
        h4.p {
                text-align: left;
        }
        /* and left-aligned but slightly indented bullet lists */
        .reveal ul {
            display: block;
            margin: 0 0 1em 3em;
        }
		/* Example titles */
		p.example {
			text-align: left;
			margin: 0 0 -0.5em 1em;
			font-weight: bold;
		}
		/* Example output style */
		pre.output {
            display: block;
            overflow-x: auto;
            padding: 0.5em;
            background: #ddd;
            color: black;
			line-height: 200%;
            -webkit-text-size-adjust: none;
		}

        </style>

		 <!-- Printing and PDF exports -->
		<script>
			var link = document.createElement( 'link' );
			link.rel = 'stylesheet';
			link.type = 'text/css';
			link.href = window.location.search.match( /print-pdf/gi ) ? '/reveal.js/css/print/pdf.css' : '/reveal.js/css/print/paper.css';
			document.getElementsByTagName( 'head' )[0].appendChild( link );
		</script>

        <!-- Needed for charts to work. Fall back to network if no local copy -->
        <script type='text/javascript' src='/jquery.min.js'></script> 
        <script>window.jQuery || document.write('<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js">\x3C/script>')</script>
        <script src="/highcharts.js"></script>
        <script>window.Highcharts || document.write('<script src="http://code.highcharts.com/highcharts.js">\x3C/script>')</script>

		<!--[if lt IE 9]>
		<script src="/reveal.js/lib/js/html5shiv.js"></script>
		<![endif]-->
	</head>

	<body>

		<div class="reveal">

			<!-- Any section element inside of this container is displayed as a slide -->
			<div class="slides">
				<section>
					<h1><?=$title?></h1>
					<h3><?=$event?></h3>
					<h3><?=$location?></h3>
					<h3><?=$date?></h3>
					<a href="<?=$url?>"><?=$url?></a><br><br>
					<p><?=$speaker?><br>
					<small><a href="http://twitter.com/<?=$twitter?>"><?=$twitter?></a></small>
					</p>
					<aside class="notes">
						<?php if(!empty($notes)) echo nl2br(trim($notes)); ?>
					</aside>
				</section>
				<?=$slides?>
			</div>

		</div>

		<script src="/reveal.js/lib/js/head.min.js"></script>
		<script src="/reveal.js/js/reveal.js"></script>

		<script>

			Reveal.initialize({
				controls: true,
				progress: true,
				history: true,
				center: true,
				width: 1024,
				height: 768,

				transition: 'slide', // none/fade/slide/convex/concave/zoom
				transitionSpeed: 'fast',

				// Optional reveal.js plugins
				dependencies: [
					{ src: '/reveal.js/lib/js/classList.js', condition: function() { return !document.body.classList; } },
					{ src: '/highlight.min.js', async: true, condition: function() { return !!document.querySelector( 'pre code' ); }, callback: function() { hljs.initHighlightingOnLoad(); } },
					{ src: '/reveal.js/plugin/zoom-js/zoom.js', async: true },
					{ src: '/reveal.js/plugin/notes/notes.js', async: true },
					{ src: '/reveal.js/plugin/line-numbers/line-numbers.js' }
				]
			});
			/* This draws the graph on the slide on a slidechanged event */
			Reveal.addEventListener('slidechanged', function(event) {
				var callback = "render_"+event.currentSlide.id;
				if(typeof(window[callback])=="function") {
					window[callback]();
				}
			} );
			/* This draws the graph if we got here directly without coming from another slide */
			Reveal.addEventListener('ready', function(event) {
				var callback = "render_"+event.currentSlide.id;
				if(typeof(window[callback])=="function") {
					window[callback]();
				}
			} );
		</script>

	</body>
</html>
