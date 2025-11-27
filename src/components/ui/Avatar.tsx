/**
 * Avatar Component
 * Displays user profile image or initials
 */

import React from 'react';
import { View, Image, StyleSheet } from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { Layout } from '../../constants/Layout';
import { ThemedText } from './ThemedText';

type AvatarSize = 'sm' | 'md' | 'lg' | 'xl';

interface AvatarProps {
  source?: string;
  name?: string;
  size?: AvatarSize;
  style?: object;
}

export function Avatar({
  source,
  name,
  size = 'md',
  style,
}: AvatarProps) {
  const { colors } = useTheme();

  const sizeMap = {
    sm: 32,
    md: 48,
    lg: 64,
    xl: 96,
  };

  const fontSizeMap = {
    sm: 12,
    md: 18,
    lg: 24,
    xl: 36,
  };

  const dimension = sizeMap[size];
  const fontSize = fontSizeMap[size];

  const getInitials = (fullName: string) => {
    const names = fullName.trim().split(' ');
    if (names.length >= 2) {
      return `${names[0][0]}${names[names.length - 1][0]}`.toUpperCase();
    }
    return names[0]?.substring(0, 2).toUpperCase() || '?';
  };

  if (source) {
    return (
      <Image
        source={{ uri: source }}
        style={[
          styles.avatar,
          {
            width: dimension,
            height: dimension,
            borderRadius: dimension / 2,
          },
          style,
        ]}
      />
    );
  }

  return (
    <View
      style={[
        styles.avatar,
        styles.placeholder,
        {
          width: dimension,
          height: dimension,
          borderRadius: dimension / 2,
          backgroundColor: colors.primaryMuted,
        },
        style,
      ]}
    >
      <ThemedText
        style={{ fontSize, color: colors.primary }}
        weight="semibold"
      >
        {name ? getInitials(name) : '?'}
      </ThemedText>
    </View>
  );
}

const styles = StyleSheet.create({
  avatar: {
    overflow: 'hidden',
  },
  placeholder: {
    alignItems: 'center',
    justifyContent: 'center',
  },
});

export default Avatar;
