/** Types partagés avec l'API Laravel (wout-job backend). */

export interface AuthUser {
  id: number;
  name: string;
  email: string;
}

export interface AuthResponse {
  message: string;
  user: AuthUser;
  token: string;
}

export type TypeContrat = 'emploi' | 'stage' | 'alternance' | 'autre';

export interface Source {
  id: number;
  nom: string;
  slug: string;
  url_base: string;
  actif: boolean;
  dernier_scrape_at?: string | null;
  offres_count?: number;
  scrape_runs?: ScrapeRun[];
}

export interface ScrapeRun {
  id: number;
  demarre_at: string;
  termine_at?: string | null;
  offres_trouvees: number;
  offres_nouvelles: number;
  offres_maj: number;
  statut: 'en_cours' | 'succes' | 'echec';
}

/** Document tel que renvoyé par Meilisearch (via RechercheOffresService). */
export interface Offre {
  id: number;
  titre: string;
  entreprise?: string | null;
  lieu?: string | null;
  type_contrat: TypeContrat;
  categorie?: string | null;
  description_texte?: string | null;
  description_html?: string | null;
  source_slug?: string;
  statut?: string;
  date_publication?: number | string | null;
  url_source?: string;
  source?: { id: number; nom: string; slug: string };
}

export interface FacetteValeurs {
  [valeur: string]: number;
}

export interface ResultatRecherche {
  donnees: Offre[];
  total: number;
  page: number;
  par_page: number;
  facettes: {
    lieu?: FacetteValeurs;
    type_contrat?: FacetteValeurs;
    categorie?: FacetteValeurs;
    source_slug?: FacetteValeurs;
  };
}

export type FrequenceAlerte = 'quotidienne' | 'hebdomadaire' | 'desactivee';

export interface RechercheSauvegardee {
  id: number;
  nom: string;
  mots_cles?: string | null;
  lieu?: string | null;
  type_contrat?: TypeContrat | null;
  categorie?: string | null;
  frequence_alerte: FrequenceAlerte;
  derniere_alerte_envoyee_at?: string | null;
  created_at: string;
}

export interface Favori {
  id: number;
  offre_id: number;
  offre?: Offre;
  created_at: string;
}

export interface Stats {
  offres_actives: number;
  offres_par_type: Record<string, number>;
  offres_par_source: Record<string, number>;
  sources_actives: number;
}
