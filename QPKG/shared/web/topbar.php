<style>
    .topbar .wrap {
        max-width: 1200px;
        margin: 0 auto;
    }

    .topbar ul {
        padding: 0;
        margin: 0 15px 0 0;
    }
    .topbar li { display: inline;
    }

    .topbar {
        z-index: 100;
        background: #fff;
        border-bottom: 1px solid #e0e0e0;
        position: fixed;
        top: 0;
        width: 100%;
        height: 60px;
    }
    .topbar-logo {
        display: inline;
        float: left;
        margin-left: 15px;
        margin-right: 15px;
    }
    .topbar-logo i {
        font-size: 20px;
        line-height: 60px;
        color: #000;
    }
    .topbar-subtitle {
        font-size:   10px;
        color:       #666;
        margin-left: 7px;
        line-height: 9px;
        display:     inline-block;
    }
    .topbar-item {
    }
    .topbar-items {
    }
    .topbar-right {
        float: right;
    }

    .topbar .topbar-item > a:not(.button) {
        color: #000;
        display: inline-block;
        text-align: center;
        text-decoration: none;
        font-size: 14px;
        font-weight: normal;
        line-height: 59px;
        padding-left: 15px;
        padding-right: 15px;
    }
    .topbar .topbar-item > a:not(.button):hover {
        background: #f8f8f8;
    }

    .topbar .topsignin {
        padding-right: 20px;
    }

    .topbar .dropdown-menu { margin-top: 12px; }

    .topbar .dropdown li a {
        height: 34px;
        line-height: 28px;
    }

    @media screen and (min-width: 0px) {
        .topbar .menupricing   { display: none; }
        .topbar .menupartners  { display: none; }
        .topbar .menucommunity { display: none; }
        .topbar .menutryfree   { display: none; }
        .topbar .menusignin    { display: none; }
        .topbar .menusignout   { display: none; }
        .topbar .menuaccount   { display: none; }
        .moremenu:before { content: "More "; }
    }
    @media screen and (max-width: 765px) {
        .topbar .toppricing    { display: none; }
        .topbar .menupricing   { display: block; }
    }
    @media screen and (max-width: 680px) {
        .topbar .topcommunity  { display: none; }
        .topbar .menucommunity { display: block; }
    }

    @media screen and (max-width: 560px) {
        .topbar .toppricing    { display: none; }
        .topbar .menupricing   { display: block; }
        .topbar .topsignin     { display: none; }
        .topbar .menusignin    { display: block; }
        .topbar .topsignout    { display: none; }
        .topbar .menusignout   { display: block; }
        .topbar .topaccount    { display: none; }
        .topbar .menuaccount   { display: block; }
        .topbar .toppartners   { display: none; }
        .topbar .menupartners  { display: block; }
        .moremenu:before       { display: block; margin-top: 19px; margin-bottom: 18px; width: 30px; height: 22px; content: " "; background: url(img/hamburger_menu.png); }
        .caret                 { display: none; }
        .topbar .dropdown      { float: right; }
        .topbar .dropdown-menu { right: 0; left: auto; margin-top: -12px; margin-right: 5px; }
    }

    @media screen and (max-width: 350px) {
        .topbar .toptryfree  { display: none;  }
        .topbar .menutryfree { display: block;}
    }

</style>

<div class="topbar">
    <div class='wrap'>
        <div class="topbar-logo">
            <a href="index.php"><i class='roonicon-roonlogo'></i> </a>
        </div>

        <ul class="topbar-items">
            <li class='topbar-item'><a href="index.php">Info</a></li>
            <li class='topbar-item'><a href="log.php">Live Log</a></li>
            <li class='topbar-item'><a href="advanced.php">Advanced</a></li>

            <li class="topbar-item dropdown">
                <a href="#" class="dropdown-toggle moremenu" data-toggle="dropdown"><span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li class='menutryfree tophidetrial hidden'><a href="/pricing.html">Try It Free</a></li>
                    <li class='menupricing'><a href="index.php">Info</a></li>
                    <li class='menupartners'><a href="log.php">Live Log</a></li>
                    <li class='menucommunity'><a href="advanced.php">Advanced</a></li>
                    <li class='menudownloads'><a target="_blank" href="https://roonlabs.com/downloads.html">Downloads</a></li>
                    <li><a target="_blank" href="https://community.roonlabs.com">Community Site / Forum</a></li>
                    <li><a target="_blank" href="https://kb.roonlabs.com/Roon_Server_on_NAS">Roon on NAS info</a></li>
                </ul>
            </li>
            <li class="topbar-item topbar-right toptryfree tophidetrial"><a class='button' style='text-transform:none; margin-top: 14px;' target="_blank" href="https://roonlabs.com/pricing.html">Try It Free</a></li>
        </ul>

    </div>
</div>
