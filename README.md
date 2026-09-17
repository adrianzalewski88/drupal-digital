# Digital Custom Module

The **Digital** module is a custom Drupal 11 module designed to provide supporting
functionality for the **DigitalTheme** Drupal 11 Bootstrap 5 subtheme.

This module works alongside:

- DigitalTheme Drupal 11 Bootstrap 5 Subtheme  
  https://github.com/fictionarts/drupal-digitaltheme

The purpose of this module is to separate custom Drupal functionality from the
theme layer, following Drupal best practices by keeping application logic inside
a custom module and presentation logic inside the theme. Drupal custom modules
are typically stored separately from themes and provide reusable functionality
across different presentations. :contentReference[oaicite:1]{index=1}

---

## Features

- Drupal 11 compatible custom module
- Provides supporting functionality for DigitalTheme
- Keeps custom PHP logic separated from theme templates
- Designed for integration with Bootstrap 5 based Drupal sites
- Built using Drupal coding standards and modern module structure

---

## Requirements

This module requires:

- Drupal 11
- DigitalTheme Drupal 11 Bootstrap 5 Subtheme

Related project:

- DigitalTheme:
  https://github.com/fictionarts/drupal-digitaltheme

DigitalTheme is built as a Bootstrap 5 Drupal subtheme. Bootstrap-based Drupal
themes commonly use subthemes to provide custom templates, styling, and
site-specific presentation while keeping reusable functionality separate. :contentReference[oaicite:2]{index=2}

## Live Demo

View Digital running in a production discovery platform environment:

https://myurbanclothing.com/


---

## Installation

### Using Git

Clone this module into your Drupal custom modules directory:

```bash
cd web/modules/custom

git clone https://github.com/fictionarts/drupal-digital.git digital
