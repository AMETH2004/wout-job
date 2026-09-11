import { Routes } from '@angular/router';
import { authGuard } from './core/guards';

export const routes: Routes = [
  {
    path: 'connexion',
    loadComponent: () => import('./pages/login/login').then((m) => m.LoginComponent),
  },
  {
    path: 'inscription',
    loadComponent: () => import('./pages/register/register').then((m) => m.RegisterComponent),
  },
  {
    path: '',
    loadComponent: () => import('./layout/shell/shell').then((m) => m.ShellComponent),
    children: [
      { path: '', pathMatch: 'full', redirectTo: 'recherche' },
      {
        path: 'recherche',
        loadComponent: () => import('./pages/recherche/recherche').then((m) => m.RechercheComponent),
      },
      {
        path: 'offres/:id',
        loadComponent: () => import('./pages/offre-detail/offre-detail').then((m) => m.OffreDetailComponent),
      },
      {
        path: 'sources',
        loadComponent: () => import('./pages/sources/sources').then((m) => m.SourcesComponent),
      },
      {
        path: 'favoris',
        canActivate: [authGuard],
        loadComponent: () => import('./pages/favoris/favoris').then((m) => m.FavorisComponent),
      },
      {
        path: 'recherches-sauvegardees',
        canActivate: [authGuard],
        loadComponent: () =>
          import('./pages/recherches-sauvegardees/recherches-sauvegardees').then(
            (m) => m.RecherchesSauvegardeesComponent,
          ),
      },
    ],
  },
  { path: '**', redirectTo: '' },
];
