<!-- Register navigation lifecycle handlers before styles can delay parsing. -->
<style>
@view-transition { navigation: none; }
@supports (-webkit-app-region: drag) {
  @view-transition { navigation: auto; }
}
</style>
<script><?php readfile(FCPATH . 'assets/js/interaction-motion.js'); ?></script>
