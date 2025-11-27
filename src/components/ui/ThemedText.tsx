/**
 * ThemedText Component
 * A Text component with theme-aware styling following Apple typography
 */

import React from 'react';
import { Text, TextProps, StyleSheet } from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { Layout } from '../../constants/Layout';

type TextVariant = 
  | 'largeTitle'
  | 'title1'
  | 'title2'
  | 'title3'
  | 'headline'
  | 'body'
  | 'callout'
  | 'subheadline'
  | 'footnote'
  | 'caption1'
  | 'caption2';

type TextColor = 'primary' | 'secondary' | 'tertiary' | 'muted' | 'inverse' | 'error' | 'success' | 'accent';

interface ThemedTextProps extends TextProps {
  variant?: TextVariant;
  color?: TextColor;
  weight?: 'regular' | 'medium' | 'semibold' | 'bold';
  align?: 'left' | 'center' | 'right';
}

export function ThemedText({
  style,
  variant = 'body',
  color = 'primary',
  weight,
  align,
  ...props
}: ThemedTextProps) {
  const { colors } = useTheme();

  // Typography styles based on Apple HIG
  const variantStyles: Record<TextVariant, object> = {
    largeTitle: {
      fontSize: Layout.fontSize.largeTitle,
      fontWeight: Layout.fontWeight.bold,
      letterSpacing: 0.37,
    },
    title1: {
      fontSize: Layout.fontSize.title1,
      fontWeight: Layout.fontWeight.bold,
      letterSpacing: 0.36,
    },
    title2: {
      fontSize: Layout.fontSize.title2,
      fontWeight: Layout.fontWeight.bold,
      letterSpacing: 0.35,
    },
    title3: {
      fontSize: Layout.fontSize.title3,
      fontWeight: Layout.fontWeight.semibold,
      letterSpacing: 0.38,
    },
    headline: {
      fontSize: Layout.fontSize.headline,
      fontWeight: Layout.fontWeight.semibold,
      letterSpacing: -0.41,
    },
    body: {
      fontSize: Layout.fontSize.body,
      fontWeight: Layout.fontWeight.regular,
      letterSpacing: -0.41,
    },
    callout: {
      fontSize: Layout.fontSize.callout,
      fontWeight: Layout.fontWeight.regular,
      letterSpacing: -0.32,
    },
    subheadline: {
      fontSize: Layout.fontSize.subheadline,
      fontWeight: Layout.fontWeight.regular,
      letterSpacing: -0.24,
    },
    footnote: {
      fontSize: Layout.fontSize.footnote,
      fontWeight: Layout.fontWeight.regular,
      letterSpacing: -0.08,
    },
    caption1: {
      fontSize: Layout.fontSize.caption1,
      fontWeight: Layout.fontWeight.regular,
      letterSpacing: 0,
    },
    caption2: {
      fontSize: Layout.fontSize.caption2,
      fontWeight: Layout.fontWeight.regular,
      letterSpacing: 0.07,
    },
  };

  // Color mapping
  const colorStyles: Record<TextColor, string> = {
    primary: colors.text,
    secondary: colors.textSecondary,
    tertiary: colors.textTertiary,
    muted: colors.textMuted,
    inverse: colors.textInverse,
    error: colors.error,
    success: colors.success,
    accent: colors.primary,
  };

  const fontWeightStyle = weight ? { fontWeight: Layout.fontWeight[weight] } : {};
  const textAlignStyle = align ? { textAlign: align } : {};

  return (
    <Text
      style={[
        variantStyles[variant],
        { color: colorStyles[color] },
        fontWeightStyle,
        textAlignStyle,
        style,
      ]}
      {...props}
    />
  );
}

export default ThemedText;
