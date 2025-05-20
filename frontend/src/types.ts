import type { RouteLocationRaw } from 'vue-router';

export type NavigationItemType = {
  name: string;
  to?: RouteLocationRaw;
  isOpen: boolean;
  children?: NavigationItemType[];
};

export { }