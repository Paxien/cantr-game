/**
 * This is helper used temporarily for React communication with the old code.
 * It does DOM manipulation and DOM event handling so they will not have to be done anywhere else.
 * It should be only in -Microfrontend components, because they are also allowed to be unclean.
 */
// @ts-nocheck

import {useEffect} from "react";

export const useDomEvent = (eventName: string, fn: ({ detail: any }) => void) => {
  useEffect(() => {
    document.body.addEventListener(eventName, fn);
    return () => document.body.removeEventListener(eventName, fn);
  }, [eventName, fn]);
};
