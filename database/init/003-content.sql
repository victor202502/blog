--
-- PostgreSQL database dump
--

\restrict Skz8djytDsj578h7nzoRLGh2w71m6rMndV6PbuVpIaYOCvESfcA7xy5YKC4CvS7

-- Dumped from database version 17.11 (7d7ea2a)
-- Dumped by pg_dump version 17.11 (Debian 17.11-1.pgdg13+2)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: posts; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.posts (id, user_id, title, content, created_at, updated_at) VALUES (1, 3, 'Die Zukunft der Künstlichen Intelligenz im Alltag', 'Künstliche Intelligenz entwickelt sich rasant und verändert bereits heute unseren Alltag. Sprachmodelle, Bilderkennung und intelligente Automatisierung helfen Unternehmen, effizienter zu arbeiten.

Auch Entwickler profitieren von KI-gestützten Werkzeugen, die Code analysieren, dokumentieren und optimieren können.

In den kommenden Jahren wird künstliche Intelligenz in nahezu jeder Branche eine zentrale Rolle spielen.', '2025-05-27 08:24:09.961293+00', '2026-07-28 06:17:36.045168+00');
INSERT INTO public.posts (id, user_id, title, content, created_at, updated_at) VALUES (2, 1, 'Moderne Webentwicklung mit HTML, CSS und JavaScript', 'Moderne Webseiten müssen schnell, responsiv und benutzerfreundlich sein.

HTML bildet die Struktur, CSS sorgt für das Design und JavaScript macht Anwendungen interaktiv.

Frameworks und moderne Entwicklungswerkzeuge ermöglichen heute professionelle Webanwendungen für Desktop und Mobilgeräte.', '2025-05-27 08:25:50.779513+00', '2026-07-28 06:17:36.096887+00');
INSERT INTO public.posts (id, user_id, title, content, created_at, updated_at) VALUES (3, 2, 'Cloud Computing: Die Zukunft moderner IT-Infrastrukturen', 'Cloud Computing gehört heute zu den wichtigsten Technologien der digitalen Transformation.

Unternehmen können Server, Datenbanken und Anwendungen flexibel bereitstellen und bezahlen nur die tatsächlich genutzten Ressourcen.

AWS, Azure und Google Cloud gehören zu den bekanntesten Cloud-Plattformen.', '2025-05-27 08:26:03.613067+00', '2026-07-28 06:17:36.14724+00');
INSERT INTO public.posts (id, user_id, title, content, created_at, updated_at) VALUES (4, 1, 'Cybersicherheit: So schützt du deine Daten im Jahr 2026', 'Cyberangriffe nehmen weltweit zu. Sichere Passwörter, Multi-Faktor-Authentifizierung und regelmäßige Updates gehören heute zu den wichtigsten Sicherheitsmaßnahmen.

Unternehmen setzen zusätzlich auf Firewalls, Penetrationstests und Security Monitoring, um ihre Systeme zu schützen.', '2025-05-27 08:26:15.762222+00', '2026-07-28 06:17:36.197077+00');


--
-- Data for Name: comments; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.comments (id, post_id, user_id, content, created_at, updated_at) VALUES (3, 1, 2, 'Sehr interessanter Artikel über künstliche Intelligenz!', '2025-05-27 08:26:31.377888+00', '2026-07-28 06:17:36.247068+00');
INSERT INTO public.comments (id, post_id, user_id, content, created_at, updated_at) VALUES (4, 1, 3, 'Ich nutze KI inzwischen täglich beim Programmieren.', '2025-05-27 08:26:31.377888+00', '2026-07-28 06:17:36.297117+00');
INSERT INTO public.comments (id, post_id, user_id, content, created_at, updated_at) VALUES (5, 1, 1, 'Machine Learning wird in Zukunft noch wichtiger werden.', '2025-05-27 08:26:31.377888+00', '2026-07-28 06:17:36.346919+00');
INSERT INTO public.comments (id, post_id, user_id, content, created_at, updated_at) VALUES (6, 2, 1, 'Responsive Design ist heute absolut unverzichtbar.', '2025-05-27 08:26:55.34388+00', '2026-07-28 06:17:36.397932+00');
INSERT INTO public.comments (id, post_id, user_id, content, created_at, updated_at) VALUES (7, 2, 4, 'JavaScript entwickelt sich jedes Jahr weiter.', '2025-05-27 08:26:55.34388+00', '2026-07-28 06:17:36.44617+00');
INSERT INTO public.comments (id, post_id, user_id, content, created_at, updated_at) VALUES (8, 3, 2, 'Wir nutzen Azure in unserem Unternehmen.', '2025-05-27 08:27:11.179292+00', '2026-07-28 06:17:36.496944+00');
INSERT INTO public.comments (id, post_id, user_id, content, created_at, updated_at) VALUES (9, 3, 5, 'Cloud Computing spart enorm viele Kosten.', '2025-05-27 08:27:11.179292+00', '2026-07-28 06:17:36.547315+00');
INSERT INTO public.comments (id, post_id, user_id, content, created_at, updated_at) VALUES (10, 4, 1, 'Cybersecurity sollte jeder ernst nehmen.', '2025-05-27 08:27:25.27619+00', '2026-07-28 06:17:36.59588+00');
INSERT INTO public.comments (id, post_id, user_id, content, created_at, updated_at) VALUES (11, 4, 2, 'Multi-Faktor-Authentifizierung ist Pflicht.', '2025-05-27 08:27:25.27619+00', '2026-07-28 06:17:36.648074+00');
INSERT INTO public.comments (id, post_id, user_id, content, created_at, updated_at) VALUES (12, 3, 1, 'Vielen Dank für diesen informativen Beitrag.', '2025-05-27 08:32:29.828231+00', '2026-07-28 06:17:36.698951+00');


--
-- Name: comments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.comments_id_seq', 12, true);


--
-- Name: posts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.posts_id_seq', 4, true);


--
-- PostgreSQL database dump complete
--

\unrestrict Skz8djytDsj578h7nzoRLGh2w71m6rMndV6PbuVpIaYOCvESfcA7xy5YKC4CvS7

