<div class="col-xs-6">
</div>
<div class="col-xs-6">
  <div class="widget-social ">
    <div class="widget-inner block_content">
      <ul class="bo-social-icons" style=" margin: 0;text-align: right;">
        {if $urls['facebook_url'] != " "}
          <li class="facebook"><a class="_blank" href="{$urls['facebook_url']}"><i class="bo-social-facebook fa fa-facebook"></i><span>Facebook</span></a></li>
        {/if}
        {if $urls['youtube_url'] != " "}
          <li class="youtube"><a class="_blank" href="{$urls['youtube_url']}"><i class="bo-social-youtube fa fa-youtube"></i><span>YouTube</span></a></li>
        {/if}
        {if $urls['instagram_url'] != " "}
          <li class="instagram"><a class="_blank" href="{$urls['instagram_url']}"><i class="bo-social-instagram fa fa-instagram"></i><span>Instagram</span></a></li>
        {/if}
      </ul>
    </div>
  </div>
</div>
