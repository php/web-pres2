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

		<!-- For syntax highlighting - note that these are not the generic highlight.js theme files - see https://github.com/nwinkler/reveal-highlight-themes -->
		<link rel="stylesheet" href="/styles/xcode.css">

		<!-- Override a few styles -->
        <style>
        /*
		   Not actually sure why this block isn't being picked up from the syntax highlight css 
		   If you change the syntax highlight theme, copy the first block here
		*/
        .reveal pre code {
                display: block;
                overflow-x: auto;
                padding: 0.5em;
                background: #fff;
                color: black;
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
        </style>

		 <!-- Printing and PDF exports -->
		<script>
			var link = document.createElement( 'link' );
			link.rel = 'stylesheet';
			link.type = 'text/css';
			link.href = window.location.search.match( /print-pdf/gi ) ? '/reveal.js/css/print/pdf.css' : '/reveal.js/css/print/paper.css';
			document.getElementsByTagName( 'head' )[0].appendChild( link );
		</script>

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

				transition: 'slide', // none/fade/slide/convex/concave/zoom
				transitionSpeed: 'fast',

				// Optional reveal.js plugins
				dependencies: [
					{ src: '/reveal.js/lib/js/classList.js', condition: function() { return !document.body.classList; } },
					{ src: '/reveal.js/plugin/markdown/marked.js', condition: function() { return !!document.querySelector( '[data-markdown]' ); } },
					{ src: '/reveal.js/plugin/markdown/markdown.js', condition: function() { return !!document.querySelector( '[data-markdown]' ); } },
					{ src: '/highlight.min.js', async: true, condition: function() { return !!document.querySelector( 'pre code' ); }, callback: function() { hljs.initHighlightingOnLoad(); } },
					{ src: '/reveal.js/plugin/zoom-js/zoom.js', async: true },
					{ src: '/reveal.js/plugin/notes/notes.js', async: true }
				]
			});

		</script>

	</body>
</html>
