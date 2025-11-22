<?php
?>
<div id="modal-confirm" class="c-modal" aria-hidden="true">
  <div class="c-modal__backdrop" data-confirm-close></div>

  <div class="c-modal__card" role="dialog" aria-modal="true" aria-labelledby="c-modal-title">
    <header class="c-modal__header">
      <h3 id="c-modal-title" class="c-modal__title">Confirmação</h3>
      <button type="button" class="c-modal__close" aria-label="Fechar" data-confirm-close>×</button>
    </header>

    <div id="c-modal-message" class="c-modal__body">
      Tem certeza desta ação?
    </div>

    <footer class="c-modal__footer">
      <button type="button" class="btn btn--ghost" data-confirm-cancel>Cancelar</button>
      <button type="button" class="btn btn--danger" data-confirm-ok>Confirmar</button>
    </footer>
  </div>
</div>
