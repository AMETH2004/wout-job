import { Component, OnInit, inject } from '@angular/core';
import { RouterLink, RouterLinkActive, RouterOutlet, Router } from '@angular/router';
import { AuthService } from '../../core/auth.service';

@Component({
  selector: 'app-shell',
  imports: [RouterOutlet, RouterLink, RouterLinkActive],
  templateUrl: './shell.html',
})
export class ShellComponent implements OnInit {
  private auth = inject(AuthService);
  private router = inject(Router);

  readonly user = this.auth.user;

  ngOnInit(): void {
    if (this.auth.getToken()) {
      this.auth.rafraichir().subscribe({ error: () => {} });
    }
  }

  deconnexion(): void {
    this.auth.logout();
    this.router.navigate(['/connexion']);
  }
}
