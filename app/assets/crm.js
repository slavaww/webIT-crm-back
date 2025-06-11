import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import App from './React/src/App';
import './React/src/assets/scss/crn.scss';

const container = document.getElementById('root');
const root = createRoot(container);

root.render(
  <React.StrictMode>
    <BrowserRouter basename="/crm">
      <App />
    </BrowserRouter>
  </React.StrictMode>
);
