@extends('layouts.site')

@section('title', 'Profil')

@section('content')
  <section class="page-head container">
    <span class="pill">Profil</span>
    <h1>Profil {{ $profile->name }}</h1>
    @if ($profile->about)
      <p>{{ $profile->about }}</p>
    @endif
  </section>

  <div class="container profile-body">
    @if ($profile->historyParagraphs())
      <article class="card card-history reveal" id="sejarah">
        <span class="card-icon"><x-glyph name="history" /></span>
        <h3>Sejarah {{ $profile->name }}</h3>
        @foreach ($profile->historyParagraphs() as $paragraph)
          <p>{{ $paragraph }}</p>
        @endforeach
      </article>
    @endif

    @if ($profile->vision || $profile->missions)
      <div class="vm-grid">
        @if ($profile->vision)
          <article class="card card-vision reveal" id="visi">
            <span class="card-icon"><x-glyph name="eye" /></span>
            <h3>Visi</h3>
            <p class="vision">{{ $profile->vision }}</p>
          </article>
        @endif

        @if ($profile->missions)
          <article class="card card-mission reveal" id="misi">
            <span class="card-icon"><x-glyph name="target" /></span>
            <h3>Misi</h3>
            <ol class="missions">
              @foreach ($profile->missions as $mission)
                <li><x-glyph name="check" :size="16" /><span>{{ $mission }}</span></li>
              @endforeach
            </ol>
          </article>
        @endif
      </div>
    @endif

    @if ($profile->core_members || $profile->divisions)
      <div class="card card-chart reveal" id="struktur">
        <h3>Struktur BPH PhotoUP</h3>
        <p class="card-sub">Badan Pengurus Harian dan anggota divisi {{ $profile->name }}.</p>
        <div class="chart">
          @php($levels = $profile->chartLevels())
          @foreach ($levels as $level => $nodes)
            @if ($level > 0)
              <span class="chart-line" aria-hidden="true"></span>
            @endif
            <div @class([
              'chart-level',
              'chart-row' => count($nodes) > 1,
              'chart-pair' => count($nodes) === 2,
              'chart-top' => $level === 0,
              // garis tengah menembus baris bercabang (mis. Sekretaris–Bendahara) menuju tingkat berikutnya
              'chart-branch' => count($nodes) > 1 && count($nodes) % 2 === 0 && $level < count($levels) - 1,
            ])>
              @foreach ($nodes as $node)
                <div class="chart-cell">
                  <div class="node">
                    <strong>{{ $node['title'] }}</strong>
                    @if ($person = $node['person'])
                      {{-- pengurus inti: foto berbingkai, lalu nama dan NPM (dari akun anggota) --}}
                      <div class="node-person">
                        @if ($person['photo'])
                          <img src="{{ $person['photo'] }}" alt="Foto {{ $person['name'] }}" class="member-photo" width="64" height="64" loading="lazy" />
                        @else
                          <span class="member-photo member-initial" aria-hidden="true">{{ mb_substr($person['name'], 0, 1) }}</span>
                        @endif
                        <span class="person-name">{{ $person['name'] }}</span>
                        @if ($person['npm'])<small>{{ $person['npm'] }}</small>@endif
                      </div>
                    @endif
                    @if ($node['note'])<small class="node-note">{{ $node['note'] }}</small>@endif
                    @if ($node['members'])
                      <ul class="node-members">
                        @foreach ($node['members'] as $member)
                          <li>
                            @if ($member['photo'])
                              <img src="{{ $member['photo'] }}" alt="Foto {{ $member['name'] }}" class="member-photo" width="64" height="64" loading="lazy" />
                            @else
                              <span class="member-photo member-initial" aria-hidden="true">{{ mb_substr($member['name'], 0, 1) }}</span>
                            @endif
                            <strong>{{ $member['name'] }}</strong>
                            @if ($member['npm'])<small>{{ $member['npm'] }}</small>@endif
                          </li>
                        @endforeach
                      </ul>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>
@endsection
