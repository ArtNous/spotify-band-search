# spotify-band-search
API para realizar busquedas de albumes de artistas y bandas.

### Pasos para utilizar la API:
1. Registrar una app en [Spotify developer](https://developer.spotify.com/dashboard/login).
2. Tomar el Client ID y el Client secret desde el Dashboard developer de Spotify.
3. Hacer un POST request al endpoint `/api/v1/token` con las credenciales anteriores enviadas en el cuerpo de la petición.
4. Comenzar a buscar artistas usando el endpoint `/api/v1/albums?q=artista`.
5. Puedes limitar la busqueda enviando el parametro `limit` por la url, y avanzar en el paginado con el parametro `offset`.