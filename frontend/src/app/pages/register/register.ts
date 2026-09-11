import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../core/auth.service';
import { NotificationService } from '../../core/notification.service';

@Component({
  selector: 'app-register',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './register.html',
  styleUrl: './register.scss',
})
export class RegisterComponent {
  private fb = inject(FormBuilder);
  private auth = inject(AuthService);
  private router = inject(Router);
  private notif = inject(NotificationService);

  readonly enCours = signal(false);

  readonly form = this.fb.nonNullable.group({
    name: ['', Validators.required],
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required, Validators.minLength(6)]],
    password_confirmation: ['', Validators.required],
  });

  soumettre(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    if (this.form.value.password !== this.form.value.password_confirmation) {
      this.notif.erreur('Les mots de passe ne correspondent pas.');
      return;
    }
    this.enCours.set(true);
    const { name, email, password, password_confirmation } = this.form.getRawValue();

    this.auth.register(name, email, password, password_confirmation).subscribe({
      next: () => {
        this.notif.succes('Compte créé, bienvenue !');
        this.router.navigateByUrl('/recherche');
      },
      error: () => this.enCours.set(false),
    });
  }
}
