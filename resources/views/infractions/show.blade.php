@extends('layouts.dashboard')

@section('title', 'Infraction #' . $infraction->id)

@section('content')
@php
    $typeLabels = [
        'speeding'          => 'Excès de vitesse',
        'red_light'         => 'Grillage de feu rouge',
        'parking'           => 'Stationnement interdit',
        'phone_use'         => 'Usage téléphone au volant',
        'seatbelt'          => 'Non port de ceinture',
        'alcohol'           => 'Alcool au volant',
        'dangerous_driving' => 'Conduite dangereuse',
        'overload'          => 'Surcharge',
        'invalid_documents' => 'Documents non valides',
        'other'             => 'Autre',
    ];
    $sourceLabels = [
        'police'             => 'Police / Gendarmerie',
        'radar'              => 'Radar automatique',
        'internal'           => 'Signalement interne',
        'reported_by_driver' => 'Signalé par le conducteur',
        'third_party'        => 'Tiers',
    ];
    $imputationLabels = [
        'company' => ['label' => 'Société', 'color' => '#3b82f6'],
        'driver'  => ['label' => 'Conducteur', 'color' => '#f59e0b'],
    ];
    $paymentColors = [
        'unpaid'    => ['bg' => '#ef444420', 'color' => '#ef4444', 'label' => 'Impayée'],
        'paid'      => ['bg' => '#10b98120', 'color' => '#10b981', 'label' => 'Payée'],
        'contested' => ['bg' => '#f59e0b20', 'color' => '#f59e0b', 'label' => 'Contestée'],
        'waived'    => ['bg' => '#94a3b820', 'color' => '#94a3b8', 'label' => 'Remise'],
    ];
    $isClosed = $infraction->status === 'closed';
@endphp

<div style="padding:1.5rem;">

    {{-- Breadcrumb --}}
    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem;font-size:.82rem;color:#64748b;">
        <a href="{{ route('infractions.index') }}" style="color:#94a3b8;text-decoration:none;">Infractions</a>
        <span>/</span>
        <span style="color:#f1f5f9;">Infraction #{{ $infraction->id }}</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;">

        {{-- Colonne principale --}}
        <div>

            {{-- Carte principale --}}
            <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.5rem;margin-bottom:1.25rem;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem;">
                    <div>
                        <h1 style="font-size:1.1rem;font-weight:700;color:#f1f5f9;margin:0 0 .35rem;">
                            {{ $typeLabels[$infraction->type] ?? $infraction->type }}
                        </h1>
                        <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                            @if($isClosed)
                            <span style="padding:.2rem .55rem;background:#94a3b820;color:#94a3b8;border-radius:.35rem;font-size:.72rem;font-weight:600;">Clôturée</span>
                            @else
                            <span style="padding:.2rem .55rem;background:#f59e0b20;color:#f59e0b;border-radius:.35rem;font-size:.72rem;font-weight:600;">Ouverte</span>
                            @endif
                            @if($infraction->payment_status && isset($paymentColors[$infraction->payment_status]))
                            @php $pc = $paymentColors[$infraction->payment_status]; @endphp
                            <span style="padding:.2rem .55rem;background:{{ $pc['bg'] }};color:{{ $pc['color'] }};border-radius:.35rem;font-size:.72rem;font-weight:600;">
                                {{ $pc['label'] }}
                            </span>
                            @endif
                            @if($infraction->auto_identified)
                            <span style="padding:.2rem .55rem;background:#10b98120;color:#10b981;border-radius:.35rem;font-size:.72rem;">Auto-identifié</span>
                            @endif
                        </div>
                    </div>
                    @if(!$isClosed)
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        @can('infractions.edit')
                        <a href="{{ route('infractions.edit', $infraction) }}"
                           style="padding:.4rem .85rem;background:#334155;color:#94a3b8;border-radius:.4rem;text-decoration:none;font-size:.82rem;">
                            Modifier
                        </a>
                        @endcan
                        @can('infractions.edit')
                        <form method="POST" action="{{ route('infractions.close', $infraction) }}" style="display:inline;"
                              onsubmit="return confirm('Clôturer cette infraction ?')">
                            @csrf
                            <button type="submit"
                                    style="padding:.4rem .85rem;background:#64748b20;color:#94a3b8;border:1px solid #475569;border-radius:.4rem;font-size:.82rem;cursor:pointer;">
                                Clôturer
                            </button>
                        </form>
                        @endcan
                    </div>
                    @endif
                </div>

                {{-- Grille infos --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem 1.5rem;">
                    <div>
                        <div style="font-size:.72rem;color:#64748b;margin-bottom:.2rem;">Véhicule</div>
                        <a href="{{ route('vehicles.show', $infraction->vehicle) }}"
                           style="font-size:.88rem;font-weight:600;color:#3b82f6;text-decoration:none;">
                            {{ $infraction->vehicle?->plate }}
                        </a>
                        <div style="font-size:.78rem;color:#94a3b8;">{{ $infraction->vehicle?->brand }} {{ $infraction->vehicle?->model }}</div>
                    </div>
                    <div>
                        <div style="font-size:.72rem;color:#64748b;margin-bottom:.2rem;">Conducteur</div>
                        @if($infraction->driver)
                        <a href="{{ route('drivers.show', $infraction->driver) }}"
                           style="font-size:.88rem;font-weight:600;color:#3b82f6;text-decoration:none;">
                            {{ $infraction->driver->full_name }}
                        </a>
                        @elseif($infraction->user)
                        <div style="font-size:.88rem;font-weight:600;color:#f1f5f9;">{{ $infraction->user->name }}</div>
                        @else
                        <div style="font-size:.88rem;color:#64748b;">Non identifié</div>
                        @endif
                    </div>
                    <div>
                        <div style="font-size:.72rem;color:#64748b;margin-bottom:.2rem;">Date / Heure</div>
                        <div style="font-size:.88rem;color:#f1f5f9;">{{ $infraction->datetime_occurred?->format('d/m/Y à H:i') }}</div>
                    </div>
                    <div>
                        <div style="font-size:.72rem;color:#64748b;margin-bottom:.2rem;">Lieu</div>
                        <div style="font-size:.88rem;color:#f1f5f9;">{{ $infraction->location ?: '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size:.72rem;color:#64748b;margin-bottom:.2rem;">Source</div>
                        <div style="font-size:.88rem;color:#f1f5f9;">{{ $sourceLabels[$infraction->source] ?? $infraction->source }}</div>
                    </div>
                    <div>
                        <div style="font-size:.72rem;color:#64748b;margin-bottom:.2rem;">Référence PV</div>
                        <div style="font-size:.88rem;color:#f1f5f9;">{{ $infraction->pv_reference ?: '—' }}</div>
                    </div>
                    @if($infraction->description)
                    <div style="grid-column:1/-1;">
                        <div style="font-size:.72rem;color:#64748b;margin-bottom:.2rem;">Description</div>
                        <p style="font-size:.85rem;color:#cbd5e1;margin:0;line-height:1.55;">{{ $infraction->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Amende & imputation --}}
            @if($infraction->fine_amount)
            <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.25rem;margin-bottom:1.25rem;">
                <h3 style="font-size:.85rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 1rem;">Amende</h3>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.75rem;margin-bottom:1rem;">
                    <div style="background:#0f172a;border-radius:.45rem;padding:.75rem;text-align:center;">
                        <div style="font-size:.72rem;color:#64748b;margin-bottom:.3rem;">Montant</div>
                        <div style="font-size:1.35rem;font-weight:700;color:#f1f5f9;">{{ number_format($infraction->fine_amount, 0, ',', ' ') }}</div>
                        <div style="font-size:.72rem;color:#94a3b8;">FCFA</div>
                    </div>
                    @if($infraction->imputation && isset($imputationLabels[$infraction->imputation]))
                    @php $imp = $imputationLabels[$infraction->imputation]; @endphp
                    <div style="background:#0f172a;border-radius:.45rem;padding:.75rem;text-align:center;">
                        <div style="font-size:.72rem;color:#64748b;margin-bottom:.3rem;">Imputation</div>
                        <div style="font-size:1rem;font-weight:700;color:{{ $imp['color'] }};">{{ $imp['label'] }}</div>
                    </div>
                    @endif
                    @if($infraction->payment_date)
                    <div style="background:#0f172a;border-radius:.45rem;padding:.75rem;text-align:center;">
                        <div style="font-size:.72rem;color:#64748b;margin-bottom:.3rem;">Payé le</div>
                        <div style="font-size:.9rem;font-weight:600;color:#10b981;">{{ $infraction->payment_date->format('d/m/Y') }}</div>
                    </div>
                    @endif
                </div>

                {{-- Action imputation --}}
                @can('infractions.impute')
                @if(!$isClosed && !$infraction->imputation)
                <div style="border-top:1px solid #334155;padding-top:1rem;">
                    <h4 style="font-size:.82rem;color:#94a3b8;margin:0 0 .75rem;">Décider l'imputation</h4>
                    <form method="POST" action="{{ route('infractions.impute', $infraction) }}">
                        @csrf
                        <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">
                            <div style="flex:1;min-width:150px;">
                                <select name="imputation" required
                                        style="width:100%;background:#0f172a;border:1px solid #475569;border-radius:.4rem;color:#f1f5f9;padding:.5rem .75rem;font-size:.85rem;">
                                    <option value="">— Imputer à —</option>
                                    <option value="company">La société</option>
                                    <option value="driver">Le conducteur</option>
                                </select>
                            </div>
                            <div style="flex:2;min-width:200px;">
                                <input type="text" name="internal_sanction" placeholder="Sanction interne (optionnel)"
                                       style="width:100%;background:#0f172a;border:1px solid #475569;border-radius:.4rem;color:#f1f5f9;padding:.5rem .75rem;font-size:.85rem;box-sizing:border-box;">
                            </div>
                            <button type="submit"
                                    style="padding:.5rem 1rem;background:#f59e0b;color:#000;border:none;border-radius:.4rem;font-size:.85rem;font-weight:600;cursor:pointer;">
                                Confirmer
                            </button>
                        </div>
                    </form>
                </div>
                @endif
                @endcan

                {{-- Action paiement --}}
                @can('infractions.edit')
                @if(!$isClosed && in_array($infraction->payment_status, ['unpaid', 'contested']))
                <div style="border-top:1px solid #334155;padding-top:1rem;margin-top:1rem;">
                    <h4 style="font-size:.82rem;color:#94a3b8;margin:0 0 .75rem;">Enregistrer le paiement</h4>
                    <form method="POST" action="{{ route('infractions.record-payment', $infraction) }}">
                        @csrf
                        <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:.65rem;align-items:end;">
                            <div>
                                <label style="font-size:.78rem;color:#64748b;display:block;margin-bottom:.25rem;">Montant payé</label>
                                <input type="number" name="fine_amount" required min="0" step="100"
                                       value="{{ old('fine_amount', $infraction->fine_amount) }}"
                                       style="width:100%;background:#0f172a;border:1px solid #475569;border-radius:.4rem;color:#f1f5f9;padding:.5rem .75rem;font-size:.85rem;box-sizing:border-box;">
                            </div>
                            <div>
                                <label style="font-size:.78rem;color:#64748b;display:block;margin-bottom:.25rem;">Date de paiement</label>
                                <input type="date" name="payment_date" required
                                       value="{{ old('payment_date', date('Y-m-d')) }}"
                                       style="width:100%;background:#0f172a;border:1px solid #475569;border-radius:.4rem;color:#f1f5f9;padding:.5rem .75rem;font-size:.85rem;box-sizing:border-box;">
                            </div>
                            <button type="submit"
                                    style="padding:.5rem 1rem;background:#10b981;color:#fff;border:none;border-radius:.4rem;font-size:.85rem;font-weight:600;cursor:pointer;">
                                Valider
                            </button>
                        </div>
                        <input type="text" name="payment_notes" placeholder="Notes de paiement (optionnel)"
                               style="margin-top:.65rem;width:100%;background:#0f172a;border:1px solid #475569;border-radius:.4rem;color:#f1f5f9;padding:.5rem .75rem;font-size:.85rem;box-sizing:border-box;">
                    </form>
                </div>
                @endif
                @endcan

                @if($infraction->payment_notes)
                <div style="border-top:1px solid #334155;padding-top:.75rem;margin-top:.75rem;">
                    <div style="font-size:.72rem;color:#64748b;margin-bottom:.25rem;">Notes de paiement</div>
                    <p style="font-size:.85rem;color:#94a3b8;margin:0;">{{ $infraction->payment_notes }}</p>
                </div>
                @endif
            </div>
            @endif

            {{-- Sanction interne --}}
            @if($infraction->internal_sanction)
            <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.25rem;margin-bottom:1.25rem;">
                <h3 style="font-size:.85rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .5rem;">Sanction interne</h3>
                <p style="font-size:.88rem;color:#cbd5e1;margin:0;line-height:1.55;">{{ $infraction->internal_sanction }}</p>
                @if($infraction->sanctionDecidedBy)
                <div style="margin-top:.5rem;font-size:.75rem;color:#64748b;">Décidée par {{ $infraction->sanctionDecidedBy->name }}</div>
                @endif
            </div>
            @endif

        </div>

        {{-- Colonne latérale --}}
        <div>

            {{-- Informations admin --}}
            <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.25rem;margin-bottom:1rem;">
                <h3 style="font-size:.85rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .9rem;">Informations</h3>
                <dl style="margin:0;display:grid;gap:.55rem;">
                    <div>
                        <dt style="font-size:.72rem;color:#64748b;">Identifiant</dt>
                        <dd style="font-size:.85rem;color:#f1f5f9;margin:0;">#{{ $infraction->id }}</dd>
                    </div>
                    @if($infraction->assignment)
                    <div>
                        <dt style="font-size:.72rem;color:#64748b;">Affectation liée</dt>
                        <dd style="font-size:.85rem;color:#f1f5f9;margin:0;">#{{ $infraction->assignment->id }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt style="font-size:.72rem;color:#64748b;">Saisie par</dt>
                        <dd style="font-size:.85rem;color:#f1f5f9;margin:0;">{{ $infraction->createdBy?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:.72rem;color:#64748b;">Date d'enregistrement</dt>
                        <dd style="font-size:.85rem;color:#f1f5f9;margin:0;">{{ $infraction->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if($infraction->updated_at != $infraction->created_at)
                    <div>
                        <dt style="font-size:.72rem;color:#64748b;">Dernière modification</dt>
                        <dd style="font-size:.85rem;color:#f1f5f9;margin:0;">{{ $infraction->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Actions --}}
            <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.25rem;margin-bottom:1rem;">
                <h3 style="font-size:.85rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .9rem;">Actions</h3>
                <div style="display:grid;gap:.5rem;">
                    <a href="{{ route('infractions.index') }}"
                       style="display:block;text-align:center;padding:.5rem;background:#334155;color:#94a3b8;border-radius:.4rem;text-decoration:none;font-size:.82rem;">
                        ← Retour à la liste
                    </a>
                    @can('infractions.edit')
                    @if(!$isClosed)
                    <a href="{{ route('infractions.edit', $infraction) }}"
                       style="display:block;text-align:center;padding:.5rem;background:#3b82f620;color:#3b82f6;border-radius:.4rem;text-decoration:none;font-size:.82rem;">
                        Modifier
                    </a>
                    @endif
                    @endcan
                    @can('infractions.edit')
                    <form method="POST" action="{{ route('infractions.destroy', $infraction) }}"
                          onsubmit="return confirm('Supprimer cette infraction ? Cette action est irréversible.')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                style="width:100%;padding:.5rem;background:#ef444415;color:#ef4444;border:1px solid #ef444430;border-radius:.4rem;font-size:.82rem;cursor:pointer;">
                            Supprimer
                        </button>
                    </form>
                    @endcan
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
