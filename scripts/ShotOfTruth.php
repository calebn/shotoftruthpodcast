<?php
namespace shotoftruth {
	class ShotOfTruth {
		const EPISODES_PER_PAGE = 10; //Changing this could break links to episodes page
		protected $site_sxe;
		function __construct(string $rss_xml) {
			if(empty($rss_xml)) {
				throw new InvalidArgumentException('rss_xml cannot be empty');
			}
			$this->site_sxe = new \SimpleXMLElement($rss_xml,LIBXML_NOCDATA);
			$this->site_sxe->registerXPathNamespace('itunes','http://www.itunes.com/dtds/podcast-1.0.dtd');
			$this->site_sxe->registerXPathNamespace('content','http://purl.org/rss/1.0/modules/content/');
		}

		public function regenSite() {
			self::writeIndexPage();
			self::writeAllEpisodePages();
			self::writeEpisodeListPage();
		}

		public function getHeaderHtml(string $title) {
			ob_start();
			?>
			<!doctype html>

			<html lang="en">
			<head>
				<!-- Required meta tags -->
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

				<!-- Bootstrap CSS -->
				<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
				<link rel="stylesheet" href="/styles.css">

				<title><?=$title?></title>
			</head>
			<body class="shot-of-truth">
				<nav class="navbar fixed-top navbar-expand-lg navbar-light bg-light">
					<a class="navbar-brand" href="#">
						<img src="images/home-logo.png" width="30" height="30" class="d-inline-block align-top" alt="">
						A Shot Of Truth Podcast
					</a>
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>

					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						<ul class="navbar-nav mr-auto">
							<li class="nav-item">
								<a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/episodes.html">Episodes</a>
							</li>
						<!--li class="nav-item">
							<a class="nav-link" href="about-us.html">About Us</a>
						</li-->
						<!-- <li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								Podcast Episodes
							</a>
							<div class="dropdown-menu" aria-labelledby="navbarDropdown">
								<?php
								$count = 0;
								foreach($this->site_sxe->channel->item as $episode) {
									?><a class="dropdown-item" href="/<?=self::getEpisodeFilename($count++)?>"><?=$episode->title?></a><?php
								}
								?>
							</div>
						</li> -->
					</ul>
					<!-- <form class="form-inline my-2 my-lg-0">
						<input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
						<button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
					</form> -->
				</div>
			</nav>

		</head>
		<body>
			<?php
			return (trim(ob_get_clean()));
		}

		public function getFooterHtml() {
			ob_start();
			?>
			<!-- Optional JavaScript -->
			<!-- jQuery first, then Popper.js, then Bootstrap JS -->
			<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
			<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
			<script>
				if(window.location.href.indexOf('/episodes') > 0){
					window.addEventListener('popstate', function(e){
						console.log(e);
						if(e.state){
							page = getPageFromUrl(e.state.href);
							console.log('pushing '+page);
							showEpisodeContainer(page,false);
						}
					});
					$(document).ready(function(){
						page = getPageFromUrl(window.location.href);
						showEpisodeContainer(page,false);
						$('.page-link').click(function(e){
							e.preventDefault();
							showEpisodeContainer($(this).attr('href').replace('#',''),true);
							window.scrollTo(0, 0);
						});
					});

					function showEpisodeContainer(page, push_state) {
						console.log('page entered: '+page);
						let prev_page = Math.max(1,parseInt(page)-1);
						console.log(prev_page);
						let next_page = Math.min($('.page-link').length -2, parseInt(page)+1);
						console.log(next_page);
						$('.episode-container').hide();
						$('#episodes-container-'+page).show();
						$('.page-item').removeClass('active');
						$('.page-num[href="#'+page+'"]').parent().addClass('active');
						$('#prev-page').attr('href','#'+prev_page);
						$('#next-page').attr('href','#'+next_page);
						let href = window.location.href.replace(window.location.hash,'')+'#'+page;
						if(push_state) {
							window.location = href;
						}
					}

					function getPageFromUrl(url) {
						console.log(url);
						page = 1;
						if(url.indexOf('#') > 0) {
							page = url.substring(url.indexOf('#')+1);
						}
						console.log(page);
						return page;
					}
				}
			</script>
		</body>
		</html>
		<?php
		return trim(ob_get_clean());
	}

	public static function getEpisodeFilename(int $episode_number) {
		return "$episode_number.html";
	}

	public static function getEpisodeDescription(\SimpleXMLElement $episode, bool $include_contact_info = false) {
		$description = $episode->xpath('content:encoded')[0];
		if(
			!$include_contact_info
			&& ($idx = strpos($description, 'Find Us:')) !== false ) {
			$description = rtrim(trim(substr($episode->xpath('content:encoded')[0], 0, $idx),'<br>'));

		}
		$description = str_replace('Show Notes:', '<span class="show-notes">Show Notes:</span>', $description);
		return trim($description);
	}

	public function getEpisodePageHtml(\SimpleXMLElement $episode) {
		$html = self::getHeaderHtml($episode->title);
		ob_start();
		?>
		<main class="episode-page">
			<section>
				<h1 class="episode-title"><?=$episode->title?></h1>
				<h2 class="episode-subtitle"><?=$episode->xpath('itunes:subtitle')[0]?></h2>
				<h4 class="episode-date"><?=date('F j, Y',strtotime($episode->pubDate))?></h2>
				<h4 class="episode-duration"><?=$episode->xpath('itunes:duration')[0]?></h2>

				<article class="description">
					<p><?=self::getEpisodeDescription($episode)?></p>
				</article>
			</section>
			<section class="player-container">
				<figure>
					<figcaption>Listen Here:</figcaption>
					<audio
						controls
						src="<?=$episode->enclosure->attributes()->url?>"
						title="Listen to the episode, <?=$episode->title?> here"
					>
					Your browser does not support playing audio
				</audio>
			</figure>
		</section>
	</main>
	<?php
	$html .= trim(ob_get_clean()) . self::getFooterHtml();
	return $html;
}

public function writeAllEpisodePages() {
	$count =0;
	foreach( $this->site_sxe->channel->item as $item) {
		$html = self::getEpisodePageHtml($item);
		file_put_contents(self::getEpisodeFilename($count++),$html);
	}

}

public function getIndexHtml() {
	$html = self::getHeaderHtml('A Shot Of Truth Podcast');
	ob_start();
	?>
	<main class="home">
		<div class="jumbotron">
			<div class="container">
				<div class="row justify-content-center">
							<!-- <div class="col-sm-4">
								<img src="/images/home-logo.png" />
							</div> -->
							<div class="col-lg-6">
								<h1 class="display-3">A Shot Of Truth</h1>
								<h2>With Victoria Matey Mendoza</h2>
								<p>This podcast is a platform for us undocumented people to share our experiences, ideas, concerns and whatever we want. Millions of people carry this identity and we all have different complex lives. It's time to pick the mic and tell our story our way.</p>
								<p>We will not let our vulnerability silence us. This is our movement and we will win.</p>
							</div>
						</div>
					</div>
				</div>
				<div class="container">
					<div class="row">
						<div class="col-lg-4">
							<div class="card m-1" >
								<div class="card-body">
									<h5 class="card-title">Where to Listen</h5>
									<h6 class="card-subtitle mb-2 text-muted">Subscribe to the Podcast</h6>
									<p class="card-text">You can find our podcast at the following places. More to come soon!</p>
									<a href="https://itunes.apple.com/us/podcast/a-shot-of-truth/id1436122328" class="card-link">Apple Podcasts</a>
									<a href="https://open.spotify.com/show/014rxomsYCAhWX98U1OMzk?si=jc7U--UbRIS0MZlCiL5vYA" class="card-link">Spotify</a>
									<a href="https://playmusic.app.goo.gl/?ibi=com.google.PlayMusic&isi=691797987&ius=googleplaymusic&apn=com.google.android.music&link=https://play.google.com/music/m/I3zqct2jo7tthib57vpvuxptr6y?t%3DA_Shot_Of_Truth%26pcampaignid%3DMKT-na-all-co-pr-mu-pod-16" class="card-link">Google Play</a>
									<a href="https://www.stitcher.com/s?fid=235842&refid=stpr" class="card-link">Stitcher</a>
									<a href="https://soundcloud.com/sudo-science/sets/a-shot-of-truth-podcast" class="card-link">SoundCloud</a>
								</div>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="card m-1" >
								<div class="card-body">
									<h5 class="card-title">Join The Cause</h5>
									<h6 class="card-subtitle mb-2 text-muted">Help Us Reach More People</h6>
									<p class="card-text">You can become a contributing member. All funds goes directly to us, the podcasters and it's creation.</p>
									<a href="https://www.patreon.com/shotoftruthpodcast" class="card-link">Donate on Patreon</a>
								</div>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="card m-1" >
								<div class="card-body">
									<h5 class="card-title">Reach Out</h5>
									<h6 class="card-subtitle mb-2 text-muted">Join the Community</h6>
									<p class="card-text">Follow, retweet, say hi. Give us feedback, ask us questions or tell us your story. We'd love to get to know you.</p>
									<a href="https://www.instagram.com/shotoftruthpodcast/" class="card-link">Instagram</a>
									<a href="https://twitter.com/shotoftruthpod" class="card-link">Twitter</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</main>
			<?php
			return $html.(trim(ob_get_clean())).self::getFooterHtml();

		}

		public function writeIndexPage() {
			file_put_contents('index.html', self::getIndexHtml());
		}

		public function getEpisodeCardHtml(\SimpleXMLElement $episode, $episode_num) {
			ob_start();
			?>
			<div class="card">
				<div class="card-body">
					<h4 class="card-title" title="<?=$episode->title?>"><?=$episode->title?></h4>
					<h6 class="card-subtitle mb-2 text-muted" title="<?=$episode->xpath('itunes:subtitle')[0]?>s"><?=$episode->xpath('itunes:subtitle')[0]?> (<?=$episode->xpath('itunes:duration')[0]?>)</h6>
					<h6 class="blog-date"><?=date('F j, Y',strtotime($episode->pubDate))?></h6>
					<p class="card-text"><?=$episode->description?></p>
					<a href="<?=self::getEpisodeFilename($episode_num)?>" class="btn btn-primary">Episode Page</a>
				</div>
			</div>
			<?php
			return trim(ob_get_clean());
		}

		public static function getEpisodeCardRowHtml(array $cards, int $page_num) {
			if($page_num < 1) {
				throw new \InvalidArgumentException("page_num must be positive");
			}
			$offset = ($page_num * self::EPISODES_PER_PAGE) - self::EPISODES_PER_PAGE;
			ob_start();
			?>
			<div id="episodes-container-<?=$page_num?>" class="episode-container center" style="display:none;" data-pagenum="<?=$page_num?>">
				<?php
				foreach($cards as $i => $card) {
					echo $card;
				}
				?>
			</div>
			<?php
			return trim(ob_get_clean());
		}

		public function getEpisodePagePagination(int $num_pages) {
			ob_start();
			?>
			<nav aria-label="Episodes Page Selector">
				<ul class="pagination justify-content-center">
					<li class="page-item">
						<a id="prev-page" class="page-link" href="#" ≈aria-label="Previous">
							<span aria-hidden="true">&laquo;</span>
							<span class="sr-only">Previous</span>
						</a>
					</li>
					<?php
					for($i=1;$i<=$num_pages;$i++) {
						?><li class="page-item"><a class="page-link page-num" href="#<?=$i?>"><?=$i?></a></li><?php
					}
					?>
					<li class="page-item">
						<a id="next-page" class="page-link" href="#" aria-label="Next">
							<span aria-hidden="true">&raquo;</span>
							<span class="sr-only">Next</span>
						</a>
					</li>
				</ul>
			</nav>
			<?php
			return trim(ob_get_clean());
		}

		public function getAllEpisodeCardsHtml() {
			$cards = [];
			$count = 0;
			foreach($this->site_sxe->channel->item as $item) {
				$cards[] = self::getEpisodeCardHtml($item, $count++);
			}
			$pages = array_chunk($cards,self::EPISODES_PER_PAGE);
			$cards_html = '';
			foreach($pages as $page_num => $cards) {
				$cards_html .= self::getEpisodeCardRowHtml($cards,$page_num+1);
			}
			$html = '';
			ob_start();
			?>
			<main class="episode-page">
				<h1 class="main-heading">Episodes</h1>
				<div id="episodes-container" class="row justify-content-center">
					<div class="col-lg-6">
						<?=$cards_html?>
						<?=self::getEpisodePagePagination(count($pages))?>
					</div>
				</div>
			</main>
			<?php
			return self::getHeaderHtml("Episodes").trim(ob_get_clean()).self::getFooterHtml();
		}

		public function writeEpisodeListPage() {
			file_put_contents('episodes.html', self::getAllEpisodeCardsHtml());
		}
	}
}