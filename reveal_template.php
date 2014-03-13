<!doctype html>
<html lang="en">

	<head>
		<meta charset="utf-8">

		<title><?=$title?></title>

		<meta name="description" content="<?=$title?>">
		<meta name="author" content="<?=$speaker?>">

		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

		<link rel="stylesheet" href="/reveal.js/css/reveal.min.css">
		<link rel="stylesheet" href="/samdark.css" id="theme">

		<!-- For syntax highlighting -->
		<link rel="stylesheet" href="/styles/github.css">

		<!-- If the query includes 'print-pdf', include the PDF print sheet -->
		<script>
			if( window.location.search.match( /print-pdf/gi ) ) {
				var link = document.createElement( 'link' );
				link.rel = 'stylesheet';
				link.type = 'text/css';
				link.href = '/reveal.js/css/print/pdf.css';
				document.getElementsByTagName( 'head' )[0].appendChild( link );
			}
		</script>

		<!--[if lt IE 9]>
		<script src="lib/js/html5shiv.js"></script>
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
		<script src="/reveal.js/js/reveal.min.js"></script>

		<script>

			// Full list of configuration options available here:
			// https://github.com/hakimel/reveal.js#configuration
			Reveal.initialize({
				controls: true,
				progress: true,
				history: true,
				center: true,
				backgroundTransition: 'none',

				theme: Reveal.getQueryHash().theme, // available themes are in /css/theme
				transition: Reveal.getQueryHash().transition || 'linear', // default/cube/page/concave/zoom/linear/fade/none
				transitionSpeed: 'fast',

				// Parallax scrolling
				// parallaxBackgroundImage: 'https://s3.amazonaws.com/hakim-static/reveal-js/reveal-parallax-1.jpg',
				// parallaxBackgroundSize: '2100px 900px',

				// Optional libraries used to extend on reveal.js
				dependencies: [
					{ src: '/reveal.js/lib/js/classList.js', condition: function() { return !document.body.classList; } },
					{ src: '/highlight.pack.js', async: true, callback: function() { hljs.initHighlightingOnLoad(); } },
					{ src: '/reveal.js/plugin/zoom-js/zoom.js', async: true, callback: function() { return !!document.body.classList; } },
					{ src: '/reveal.js/plugin/notes/notes.js', async: true, condition: function() { return !!document.body.classList; } }
				]
			});

		</script>

	</body>
</html>
