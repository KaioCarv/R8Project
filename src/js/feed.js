
(function () {
  
  window.toastSuccess = window.toastSuccess || function (msg) { console.log('[success]', msg); };
  window.toastError   = window.toastError   || function (msg) { alert(msg); };
  window.toastInfo    = window.toastInfo    || function (msg) { console.log('[info]', msg); };
  window.toastWarn    = window.toastWarn    || function (msg) { alert(msg); };


  function askConfirm(message, opts) {
    if (typeof window.confirmModal === 'function') {
      return window.confirmModal(message, opts);
    }
    return Promise.resolve(window.confirm(message));
  }

  
  document.addEventListener('submit', function (ev) {
    var form = ev.target.closest('.feed-comment-form');
    if (!form) return;

    ev.preventDefault();

    var avaliacaoId = form.getAttribute('data-avaliacao-id');
    var input       = form.querySelector('.feed-comment-input');
    if (!avaliacaoId || !input) return;

    var texto = input.value.trim();
    if (!texto) {
      toastWarn('Digite um comentário.');
      return;
    }

    var fd = new FormData();
    fd.append('action', 'criar_comentario');
    fd.append('avaliacao_id', avaliacaoId);
    fd.append('texto', texto);

    fetch('api/avaliacao.php', {
      method: 'POST',
      body: fd
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.status) {
          toastError('Erro inesperado ao comentar.');
          return;
        }

        if (data.status === 'not_logged') {
          toastInfo('Você precisa estar logado para comentar.');
          return;
        }

        if (data.status === 'empty') {
          toastWarn('O comentário não pode ficar vazio.');
          return;
        }

        if (data.status === 'ok' && data.comentario) {
          input.value = '';

          var c     = data.comentario;
          var lista = form.parentElement;
          var nome  = c.nome_usuario || c.nome || 'Você';

          var div = document.createElement('div');
          div.className = 'comment-row';
          div.setAttribute('data-comentario-id', c.id);

          div.innerHTML = `
            <div class="comment-main">
              <span class="comment-author">${nome}</span>
              <span class="comment-text">${c.texto}</span>
            </div>
            <div class="comment-actions">
              <button type="button"
                      class="comment-like-btn"
                      data-like-comentario="${c.id}">
                <span class="comment-like-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart">
                    <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/>
                  </svg>
                </span>
                <span class="comment-like-count">${c.num_curtidas || 0}</span>
              </button>
              <button type="button"
                      class="comment-icon-btn"
                      data-action="editar-comentario"
                      data-comentario-id="${c.id}"
                      title="Editar">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil">
                  <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                  <path d="m15 5 4 4"/>
                </svg>
              </button>
              <button type="button"
                      class="comment-icon-btn"
                      data-action="excluir-comentario"
                      data-comentario-id="${c.id}"
                      title="Excluir">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-icon lucide-trash">
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                  <path d="M3 6h18"/>
                  <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
              </button>
            </div>
          `;

          lista.insertBefore(div, form);
          toastSuccess('Comentário publicado!');
        } else {
          toastError(data.message || 'Erro ao criar comentário.');
        }
      })
      .catch(function () {
        toastError('Erro na requisição de comentário.');
      });
  });


  document.addEventListener('click', function (ev) {
    
    var btnAval = ev.target.closest('[data-like-avaliacao]');
    if (btnAval) {
      var id = btnAval.getAttribute('data-like-avaliacao');
      curtirAvaliacao(btnAval, id);
      return;
    }

 
    var btnComent = ev.target.closest('[data-like-comentario]');
    if (btnComent) {
      var idC = btnComent.getAttribute('data-like-comentario');
      curtirComentario(btnComent, idC);
      return;
    }

    
    var openCommentBtn = ev.target.closest('.feed-comment-toggle');
    if (openCommentBtn) {
      var card  = openCommentBtn.closest('.feed-card');
      if (card) {
        var input = card.querySelector('.feed-comment-input');
        if (input) {
          input.focus();
          input.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }
      return;
    }

 
    var editBtn = ev.target.closest('[data-action="editar-comentario"]');
    if (editBtn) {
      editarComentario(editBtn);
      return;
    }

    var delBtn = ev.target.closest('[data-action="excluir-comentario"]');
    if (delBtn) {
      excluirComentario(delBtn);
      return;
    }
  });

 
  document.addEventListener('keydown', function (ev) {
    var input = ev.target.closest('.comment-edit-input');
    if (!input) return;

    if (ev.key === 'Enter') {
      ev.preventDefault();
      var row = input.closest('.comment-row');
      if (!row) return;

      var btn = row.querySelector('[data-action="editar-comentario"]');
      if (btn) {
        editarComentario(btn);
      }
    }
  });

  
  function curtirAvaliacao(btn, id) {
    if (!id) return;

    var fd = new FormData();
    fd.append('action', 'curtir_avaliacao');
    fd.append('avaliacao_id', id);

    fetch('api/avaliacao.php', {
      method: 'POST',
      body: fd
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.status) return;

        if (data.status === 'not_logged') {
          toastInfo('Você precisa estar logado para curtir.');
          return;
        }

        if (data.status === 'already_liked') {
          toastInfo('Você já curtiu esta avaliação.');
          btn.classList.add('is-liked');
          return;
        }

        if (data.status !== 'ok') return;

        var span = btn.querySelector('.feed-like-count');
        if (span) span.textContent = data.curtidas;


        btn.classList.add('is-liked');
      })
      .catch(function () {});
  }

  function curtirComentario(btn, id) {
    if (!id) return;

    var fd = new FormData();
    fd.append('action', 'curtir_comentario');
    fd.append('comentario_id', id);

    fetch('api/avaliacao.php', {
      method: 'POST',
      body: fd
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.status) return;

        if (data.status === 'not_logged') {
          toastInfo('Você precisa estar logado para curtir.');
          return;
        }

        if (data.status === 'already_liked') {
          toastInfo('Você já curtiu este comentário.');
          btn.classList.add('is-liked');
          return;
        }

        if (data.status !== 'ok') return;

        var span = btn.querySelector('.comment-like-count');
        if (span) span.textContent = data.curtidas;

        btn.classList.add('is-liked');
      })
      .catch(function () {});
  }

  
  function editarComentario(btn) {
    var comentarioId = btn.getAttribute('data-comentario-id');
    if (!comentarioId) return;

    var row    = btn.closest('.comment-row');
    if (!row) return;

    var textEl = row.querySelector('.comment-text');
    if (!textEl) return;

    var input  = row.querySelector('.comment-edit-input');

  
    if (!row.classList.contains('is-editing')) {
      var atual = textEl.textContent.trim();

      input = document.createElement('input');
      input.type = 'text';
      input.className = 'comment-edit-input';
      input.value = atual;

      row.classList.add('is-editing');
      textEl.style.display = 'none';
      textEl.parentElement.appendChild(input);

      input.focus();
      input.setSelectionRange(atual.length, atual.length);

      btn.setAttribute('data-edit-mode', 'saving');
      btn.setAttribute('title', 'Salvar');

      return;
    }

   
    if (!input) return;

    var novo = input.value.trim();
    if (!novo) {
      toastWarn('O comentário não pode ficar vazio.');
      return;
    }

    var fd = new FormData();
    fd.append('action', 'editar_comentario');
    fd.append('comentario_id', comentarioId);
    fd.append('texto', novo);

    fetch('api/avaliacao.php', {
      method: 'POST',
      body: fd
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.status) {
          toastError('Erro inesperado ao editar comentário.');
          return;
        }

        if (data.status === 'not_logged') {
          toastInfo('Você precisa estar logado para editar.');
        } else if (data.status === 'forbidden') {
          toastInfo('Você só pode editar seus próprios comentários.');
        } else if (data.status === 'empty') {
          toastWarn('O comentário não pode ficar vazio.');
        } else if (data.status === 'ok') {
          textEl.textContent = novo;
          textEl.style.display = '';
          input.remove();
          row.classList.remove('is-editing');
          btn.removeAttribute('data-edit-mode');
          btn.setAttribute('title', 'Editar');
          toastSuccess('Comentário atualizado!');
        } else {
          toastError(data.message || 'Erro ao editar comentário.');
        }
      })
      .catch(function () {
        toastError('Erro na requisição de edição.');
      });
  }

 
function excluirComentario(btn) {
  var comentarioId = btn.getAttribute('data-comentario-id');
  if (!comentarioId) return;

  var askPromise = (typeof window.showConfirm === 'function')
    ? window.showConfirm({
        title: 'Excluir comentário',
        message: 'Deseja realmente excluir este comentário?',
        confirmText: 'Excluir',
        cancelText: 'Cancelar',
        variant: 'danger'
      })
    : Promise.resolve(window.confirm('Deseja realmente excluir este comentário?'));

  askPromise.then(function (ok) {
    if (!ok) return;

    var fd = new FormData();
    fd.append('action', 'excluir_comentario');
    fd.append('comentario_id', comentarioId);

    fetch('api/avaliacao.php', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.status) {
          toastError('Erro inesperado ao excluir comentário.');
          return;
        }

        if (data.status === 'not_logged') {
          toastInfo('Você precisa estar logado para excluir.');
        } else if (data.status === 'forbidden') {
          toastInfo('Você só pode excluir seus próprios comentários.');
        } else if (data.status === 'ok') {
          var row = btn.closest('.comment-row');
          if (row) row.remove();
          toastSuccess('Comentário excluído.');
        } else {
          toastError(data.message || 'Erro ao excluir comentário.');
        }
      })
      .catch(function () {
        toastError('Erro na requisição de exclusão.');
      });
  });
}

})();
