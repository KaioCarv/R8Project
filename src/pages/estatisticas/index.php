<?php 


require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/../../api/perfil.php';
require_once __DIR__ . '/../../api/estatisticas.php';

if (!isset($_SESSION['user_id'])) {
  ?>
  <section class="profile-page">
    <p>Você precisa estar logado para ver as estatísticas.</p>
  </section>
  <?php
  return;
}

$userId = (int)$_SESSION['user_id'];


$perfil = api_perfil_getPerfil($conn, $userId);

if (!$perfil) {
  ?>
  <section class="profile-page">
    <p>Não foi possível carregar os dados do perfil.</p>
  </section>
  <?php
  return;
}

$displayName = !empty($perfil['nome_usuario'])
  ? $perfil['nome_usuario']
  : $perfil['nome'];

$bio = $perfil['biografia']
  ?: 'Adicione uma biografia para contar mais sobre você.';

$avatarLetter = strtoupper(substr($displayName, 0, 1));


$stats       = api_stats_getResumo($conn, $userId);
$qtdAval     = api_stats_getQtdAvaliacoes($conn, $userId);
$mediaNotas  = (float)$stats['media_notas'];
$filmes      = (int)$stats['num_filmes_assistidos'];
$series      = (int)$stats['num_series_assistidas'];
?>

<section class="profile-page">
 
  <div class="profile-tabs">

    <a href="?page=perfil"
       class="profile-tab <?= $page === 'perfil' ? 'profile-tab--active' : '' ?>"
       aria-label="Perfil">
      <svg xmlns="http://www.w3.org/2000/svg"
           viewBox="0 0 24 24"
           class="profile-tab-icon"
           aria-hidden="true">
        <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round" />
      </svg>
    </a>


    <a href="?page=stats"
       class="profile-tab <?= $page === 'stats' ? 'profile-tab--active' : '' ?>"
       aria-label="Estatísticas pessoais">
      <svg xmlns="http://www.w3.org/2000/svg"
           viewBox="0 0 24 24"
           class="profile-tab-icon"
           aria-hidden="true">
        <path d="M3 3v16a2 2 0 0 0 2 2h16"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round" />
        <path d="m19 9-5 5-4-4-3 3"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round" />
      </svg>
    </a>
  </div>

 
  <section class="profile-card">
    <div class="profile-header">
      <div class="profile-avatar">
        <?= htmlspecialchars($avatarLetter) ?>
      </div>

      <div class="profile-info">
        <h1 class="profile-name">
          <?= htmlspecialchars($displayName) ?>
        </h1>
        <p class="profile-bio">
          <?= nl2br(htmlspecialchars($bio)) ?>
        </p>
      </div>
    </div>
  </section>

 
  <section class="profile-section stats-main-section">
    <header class="profile-section-header">
      <h2>Estatísticas Pessoais</h2>
    </header>

    <div class="stats-card">
      <div class="stats-grid">
        
        <div class="stats-summary">
          <div class="stats-summary-item">
            <div class="stats-summary-label">Média de notas</div>
            <div class="stats-summary-value">
              <?= number_format($mediaNotas, 1, ',', '.') ?>
            </div>
          </div>

          <div class="stats-summary-item">
            <div class="stats-summary-label">Filmes assistidos</div>
            <div class="stats-summary-value">
              <?= $filmes ?>
            </div>
          </div>

          <div class="stats-summary-item">
            <div class="stats-summary-label">Séries assistidas</div>
            <div class="stats-summary-value">
              <?= $series ?>
            </div>
          </div>

          <div class="stats-summary-item">
            <div class="stats-summary-label">Avaliações publicadas</div>
            <div class="stats-summary-value">
              <?= $qtdAval ?>
            </div>
          </div>
        </div>

     
        <div class="stats-chart">
          <h3 class="stats-chart-title">Gêneros mais assistidos</h3>

          <div class="stats-chart-body">
            <div class="stats-chart-pie"></div>

            <ul class="stats-chart-legend">
              <li><span class="bullet bullet--yellow"></span> Ficção Científica</li>
              <li><span class="bullet bullet--orange"></span> Drama</li>
              <li><span class="bullet bullet--light"></span> Comédia</li>
              <li><span class="bullet bullet--blue"></span> Ação</li>
              <li><span class="bullet bullet--gray"></span> Suspense</li>
            </ul>
          </div>
        </div>

    
        <div class="stats-calendar">
          <header class="stats-calendar-header">
            <button class="stats-calendar-nav" aria-label="Mês anterior">‹</button>
            <h3>Outubro 2025</h3>
            <button class="stats-calendar-nav" aria-label="Próximo mês">›</button>
          </header>

          <div class="stats-calendar-grid">
            <div class="stats-calendar-weekdays">
              <span>DOM</span><span>SEG</span><span>TER</span><span>QUA</span>
              <span>QUI</span><span>SEX</span><span>SAB</span>
            </div>

            <div class="stats-calendar-days">
              <span class="is-empty">28</span>
              <span class="is-empty">29</span>
              <span class="is-empty">30</span>

              <span class="is-active">1</span>
              <span class="is-active">2</span>
              <span class="is-active">3</span>
              <span class="is-active">4</span>

              <span>5</span>
              <span class="is-active">6</span>
              <span class="is-active">7</span>
              <span>8</span>
              <span class="is-active">9</span>
              <span class="is-active">10</span>
              <span>11</span>

              <span class="is-active">12</span>
              <span>13</span>
              <span>14</span>
              <span class="is-active">15</span>
              <span>16</span>
              <span class="is-active">17</span>
              <span>18</span>

              <span class="is-active">19</span>
              <span>20</span>
              <span>21</span>
              <span>22</span>
              <span>23</span>
              <span>24</span>
              <span>25</span>

              <span>26</span>
              <span>27</span>
              <span>28</span>
              <span>29</span>
              <span>30</span>
              <span>31</span>
              <span class="is-empty">1</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</section>
