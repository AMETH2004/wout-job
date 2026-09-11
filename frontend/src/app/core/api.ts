import { HttpParams } from '@angular/common/http';

/** Construit un HttpParams en ignorant null / undefined / ''. */
export function toParams(obj: object): HttpParams {
  let params = new HttpParams();
  for (const [cle, valeur] of Object.entries(obj) as [string, unknown][]) {
    if (valeur !== null && valeur !== undefined && valeur !== '') {
      params = params.set(cle, String(valeur));
    }
  }
  return params;
}

export interface EcritureReponse<T> {
  message: string;
  data: T;
}
