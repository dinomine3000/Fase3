<?php 
include_once( dirname(__DIR__) . "../../Lib/lang/translator.php" );
if (!isset($_SESSION)) {
    session_start();
}
$isLoggedIn = isset($_SESSION['id']);
$clientName = $isLoggedIn ? $_SESSION['username'] : null;

?>

<header class="site-header">
  <div class="container-lg py-0">
    <div class="d-flex align-items-center gap-3" style="height:56px">
      <a class="logo" href="/works/webapp/home.php"><?php echo lang("portal");?> <span class="logo-wiki"><?php echo lang("wiki");?></span></a>
      <div class="search-outer">
        <div class="search-wrap">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="searchInput" placeholder="<?php echo lang("search_wiki");?>"
                 oninput="searchAllHeader(this.value,'hdr-suggest','wiki/')"
                 onblur="setTimeout(()=>{let s=document.getElementById('hdr-suggest');if(s){s.innerHTML='';s.classList.remove('has-results');}},150)"
                 onkeydown="if(event.key==='Enter'&&this.value.trim())location.href='/works/webapp/wiki/search.php?q='+encodeURIComponent(this.value.trim())">
        </div>
        <div id="hdr-suggest" class="search-suggest"></div>
      </div>
      <button class="theme-toggle" onclick="toggleTheme()" title="<?php echo lang("toggle_theme");?>">
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="icon-sun"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      <a href="/works/webapp/foruns/forum.php" class="hbtn primary" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <?php echo lang("forum");?>
      </a>
      <?php if ($isLoggedIn): ?>
      <a href="/works/webapp/wiki/profile.php?user=<?php echo urlencode($clientName); ?>" class="hbtn icon" style="text-decoration:none" title="<?php echo lang("my_profile");?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </a>
      <form action="/works/webapp/auth/logout.php" method="POST" style="margin:0">
        <button type="submit" class="hbtn icon" title="Logout">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </form>
      <?php else: ?>
      <a href="/works/webapp/auth/formLogin.php" class="hbtn" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <?php echo lang("login");?>
      </a>
      <?php endif; ?>
    </div>
  </div>
</header>